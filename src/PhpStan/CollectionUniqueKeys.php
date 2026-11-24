<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Parser\Parser;
use PHPStan\Parser\ParserErrorsException;
use PHPStan\Reflection\ReflectionProvider;

/**
 * Tells whether columns filtered with an equality cover a unique or primary key of a collection's main
 * table, so that at most one row matches.
 *
 * The main table is read from code, never guessed: the collection's _construct() (or a parent's) calls
 * _init($model, $resourceModel), and the resource model's _construct() calls _init('table', 'id').
 * Keys come from db_schema.xml through the project index, and from Setup/InstallSchema.php and
 * Setup/UpgradeSchema.php of the module that holds the resource model, as Magento before 2.3 declared them,
 * unless a db_schema.xml declares the table with a primary key.
 */
final class CollectionUniqueKeys
{
    private const SETUP_SCRIPTS = ['Setup/InstallSchema.php', 'Setup/UpgradeSchema.php'];

    /** @var array<string, ?array{string, string}> class => [main table, file of the resource model] */
    private array $tables = [];

    /** @var array<string, array<string, list<list<string>>>> module root => table => keys */
    private array $setupKeys = [];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly Parser $parser,
        private readonly ProjectIndexProvider $index,
    ) {
    }

    /**
     * @param list<string> $columns lower case
     */
    public function coverAUniqueKey(string $collectionClass, array $columns): bool
    {
        if ($columns === []) {
            return false;
        }
        $class = strtolower($collectionClass);
        if (!array_key_exists($class, $this->tables)) {
            $this->tables[$class] = $this->mainTable($collectionClass);
        }
        $table = $this->tables[$class];
        if ($table === null) {
            return false;
        }
        [$name, $resourceFile] = $table;
        $index = $this->index->get();
        // A db_schema.xml that creates the table, with its primary key, describes the whole table: keys that only
        // setup scripts add do not count on it. One that only adds columns to a table a setup script creates keeps
        // that script's keys.
        $keys = $index->uniqueKeysOf($name);
        if (!$index->tableHasPrimaryKey($name)) {
            $keys = [...$keys, ...$this->keysFromSetupScripts($resourceFile)[$name] ?? []];
        }
        foreach ($keys as $key) {
            if ($key !== [] && array_diff($key, $columns) === []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ?array{string, string}
     */
    private function mainTable(string $collectionClass): ?array
    {
        $resourceModel = $this->initArgument($collectionClass, 1);
        if ($resourceModel === null) {
            return null;
        }
        $table = $this->initArgument($resourceModel[0], 0);

        return $table === null ? null : [strtolower($table[0]), $table[1]];
    }

    /**
     * The string at $position of the first $this->_init() call in _construct() of the class or its
     * nearest parent that has one, with the file of that class. A _construct() without _init() that
     * does not call parent::_construct() replaces the parent's: then the string is unknown.
     *
     * @return ?array{string, string}
     */
    private function initArgument(string $class, int $position): ?array
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return null;
        }
        $reflection = $this->reflectionProvider->getClass($class);
        foreach ([$reflection, ...$reflection->getParents()] as $candidate) {
            $file = $candidate->getFileName();
            if ($file === null) {
                continue;
            }
            $classNode = $this->classNode($file, $candidate->getName());
            $construct = $classNode?->getMethod('_construct');
            if ($classNode === null || $construct === null) {
                continue;
            }
            foreach ((new NodeFinder())->findInstanceOf($construct->stmts ?? [], Expr\MethodCall::class) as $call) {
                if ($call->var instanceof Expr\Variable && $call->var->name === 'this'
                    && $call->name instanceof Node\Identifier && $call->name->toLowerString() === '_init'
                ) {
                    $value = $this->stringValue(CallArgs::of($call)[$position]->value ?? null, $classNode);

                    return $value === null ? null : [ltrim($value, '\\'), $file];
                }
            }
            if (!$this->callsParentConstruct($construct)) {
                return null;
            }
        }

        return null;
    }

    private function callsParentConstruct(Stmt\ClassMethod $construct): bool
    {
        // Only a call that always runs: one inside an "if" may not.
        foreach ($construct->stmts ?? [] as $statement) {
            $call = $statement instanceof Stmt\Expression || $statement instanceof Stmt\Return_ ? $statement->expr : null;
            if ($call instanceof Expr\StaticCall && $call->class instanceof Node\Name && $call->class->toLowerString() === 'parent'
                && $call->name instanceof Node\Identifier && $call->name->toLowerString() === '_construct'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * A string literal, Foo::class, or a string constant of the class itself.
     */
    private function stringValue(?Expr $expr, Stmt\Class_ $class): ?string
    {
        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value;
        }
        if (!$expr instanceof Expr\ClassConstFetch || !$expr->class instanceof Node\Name || !$expr->name instanceof Node\Identifier) {
            return null;
        }
        if ($expr->name->toLowerString() === 'class') {
            return $expr->class->toString();
        }
        if (!in_array($expr->class->toLowerString(), ['self', 'static'], true)) {
            return null;
        }
        foreach ($class->getConstants() as $constants) {
            foreach ($constants->consts as $constant) {
                if ($constant->name->toString() === $expr->name->toString() && $constant->value instanceof Node\Scalar\String_) {
                    return $constant->value->value;
                }
            }
        }

        return null;
    }

    private function classNode(string $file, string $className): ?Stmt\Class_
    {
        foreach ((new NodeFinder())->findInstanceOf($this->parse($file), Stmt\Class_::class) as $class) {
            if ($class->namespacedName !== null && strcasecmp($class->namespacedName->toString(), $className) === 0) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @return Stmt[]
     */
    private function parse(string $file): array
    {
        try {
            return $this->parser->parseFile($file);
        } catch (ParserErrorsException) {
            return [];
        }
    }

    /**
     * @return array<string, list<list<string>>> table => keys
     */
    private function keysFromSetupScripts(string $resourceFile): array
    {
        $root = $this->moduleRoot($resourceFile);
        if ($root === null) {
            return [];
        }
        if (isset($this->setupKeys[$root])) {
            return $this->setupKeys[$root];
        }
        $keys = [];
        foreach (self::SETUP_SCRIPTS as $script) {
            if (is_file($root . '/' . $script)) {
                $keys = $this->keysFromSetupScript($root . '/' . $script, $keys);
            }
        }

        return $this->setupKeys[$root] = $keys;
    }

    /**
     * Unique and primary indexes that addIndex() adds, on a table that newTable() builds or on the
     * connection, and the primary columns of newTable(), after the keys of earlier scripts. dropIndex()
     * removes the key that getIdxName() names, or every key of the table when the name cannot be read.
     *
     * @param array<string, list<list<string>>> $keys table => keys
     * @return array<string, list<list<string>>> table => keys
     */
    private function keysFromSetupScript(string $file, array $keys): array
    {
        $primary = [];
        /** @var array<string, ?string> $variables variable => table that newTable() built into it */
        $variables = [];
        // Source order: an assignment comes before the calls in its chain and before later uses of the variable.
        $nodes = (new NodeFinder())->find($this->parse($file), static fn (Node $node): bool => $node instanceof Expr\Assign || $node instanceof Expr\MethodCall);
        foreach ($nodes as $node) {
            if ($node instanceof Expr\Assign) {
                if ($node->var instanceof Expr\Variable && is_string($node->var->name)) {
                    $variables[$node->var->name] = $this->newTableOf($node->expr, []);
                }
                continue;
            }
            if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier) {
                continue;
            }
            $name = $node->name->toLowerString();
            $args = CallArgs::of($node);
            $table = $this->newTableOf($node->var, $variables);
            if ($name === 'addindex' && $table !== null) {
                // Table::addIndex($indexName, $fields, $options)
                $fields = $this->columns($args[1]->value ?? null);
                if ($fields !== null && $this->isUniqueOption($args[2]->value ?? null)) {
                    $keys[$table][] = $fields;
                }
            } elseif ($name === 'addindex') {
                // AdapterInterface::addIndex($tableName, $indexName, $fields, $indexType)
                $table = $this->tableName($args[0]->value ?? null);
                $fields = $this->columns($args[2]->value ?? null);
                if ($table !== null && $fields !== null && $this->isUniqueType($args[3]->value ?? null)) {
                    $keys[$table][] = $fields;
                }
            } elseif ($name === 'dropindex' && ($dropped = $this->tableName($args[0]->value ?? null)) !== null) {
                // AdapterInterface::dropIndex($tableName, $keyName), the name usually from getIdxName($table, $fields, ...)
                // A primary key built earlier in this script can be dropped too.
                if (isset($primary[$dropped])) {
                    $keys[$dropped][] = array_keys($primary[$dropped]);
                    unset($primary[$dropped]);
                }
                $index = $args[1]->value ?? null;
                $fields = $index instanceof Expr\MethodCall && $index->name instanceof Node\Identifier && $index->name->toLowerString() === 'getidxname'
                    ? $this->columns(CallArgs::of($index)[1]->value ?? null) : null;
                $keys[$dropped] = $fields === null ? [] : array_values(array_filter($keys[$dropped] ?? [], static fn (array $key): bool => $key !== $fields));
            } elseif ($name === 'addcolumn' && $table !== null) {
                // Table::addColumn($name, $type, $size, $options)
                $column = $args[0]->value ?? null;
                if ($column instanceof Node\Scalar\String_ && $this->hasPrimaryOption($args[3]->value ?? null)) {
                    $primary[$table][strtolower($column->value)] = true;
                }
            }
        }
        foreach ($primary as $table => $columns) {
            $keys[$table][] = array_keys($columns);
        }

        return $keys;
    }

    /**
     * The table of the newTable() call this chain starts from, directly or through a variable.
     *
     * @param array<string, ?string> $variables
     */
    private function newTableOf(Expr $expr, array $variables): ?string
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'newtable') {
                return $this->tableName(CallArgs::of($expr)[0]->value ?? null);
            }
        }

        return $expr instanceof Expr\Variable && is_string($expr->name) ? $variables[$expr->name] ?? null : null;
    }

    /**
     * 'table' or $setup->getTable('table').
     */
    private function tableName(?Expr $expr): ?string
    {
        if ($expr instanceof Expr\MethodCall && $expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'gettable') {
            $expr = CallArgs::of($expr)[0]->value ?? null;
        }

        return $expr instanceof Node\Scalar\String_ ? strtolower($expr->value) : null;
    }

    /**
     * 'column' or ['column', ...] with string literals only.
     *
     * @return ?list<string>
     */
    private function columns(?Expr $expr): ?array
    {
        if ($expr instanceof Node\Scalar\String_) {
            return [strtolower($expr->value)];
        }
        if (!$expr instanceof Expr\Array_ || $expr->items === []) {
            return null;
        }
        $columns = [];
        foreach ($expr->items as $item) {
            // @phpstan-ignore identical.alwaysFalse (php-parser 4, bundled with PHPStan 1.12, allows null items)
            if ($item === null || !$item->value instanceof Node\Scalar\String_) {
                return null;
            }
            $columns[] = strtolower($item->value->value);
        }

        return $columns;
    }

    private function isUniqueOption(?Expr $options): bool
    {
        return $this->isUniqueType($this->option($options, 'type'));
    }

    private function hasPrimaryOption(?Expr $options): bool
    {
        $value = $this->option($options, 'primary');

        return $value instanceof Expr\ConstFetch && $value->name->toLowerString() === 'true';
    }

    private function option(?Expr $options, string $key): ?Expr
    {
        foreach ($options instanceof Expr\Array_ ? $options->items : [] as $item) {
            // @phpstan-ignore notIdentical.alwaysTrue (php-parser 4, bundled with PHPStan 1.12, allows null items)
            if ($item !== null && $item->key instanceof Node\Scalar\String_ && strtolower($item->key->value) === $key) {
                return $item->value;
            }
        }

        return null;
    }

    /**
     * AdapterInterface::INDEX_TYPE_UNIQUE or INDEX_TYPE_PRIMARY, or their values.
     */
    private function isUniqueType(?Expr $type): bool
    {
        if ($type instanceof Expr\ClassConstFetch && $type->name instanceof Node\Identifier) {
            return in_array($type->name->toString(), ['INDEX_TYPE_UNIQUE', 'INDEX_TYPE_PRIMARY'], true);
        }

        return $type instanceof Node\Scalar\String_ && in_array(strtolower($type->value), ['unique', 'primary'], true);
    }

    /**
     * The nearest directory above the file with etc/module.xml.
     */
    private function moduleRoot(string $file): ?string
    {
        for ($dir = dirname($file); $dir !== dirname($dir); $dir = dirname($dir)) {
            if (is_file($dir . '/etc/module.xml')) {
                return $dir;
            }
        }

        return null;
    }
}
