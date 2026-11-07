<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Tracks, per collection and in source order within a function, whether a limit was
 * set, whether the collection was already loaded, and whether it was filtered on a
 * unique key. Marks getFirstItem() calls and count() calls with what applies.
 *
 * A collection is identified by the root of its call chain: "$var" or "$this->prop".
 * "Earlier" means earlier in the file; there is no control-flow analysis.
 */
final class CollectionStateVisitor extends NodeVisitorAbstract
{
    public const LIMITED = 'magentoPerfCollectionLimited';
    public const LOADED = 'magentoPerfCollectionLoaded';
    public const UNIQUE_FILTER = 'magentoPerfCollectionUniqueFilter';

    /** Set on count() and getFirstItem() when the same function uses the collection's rows elsewhere. */
    public const ROWS_USED_ELSEWHERE = 'magentoPerfCollectionRowsUsedElsewhere';

    /** Set on getCategoryIds() after addCategoryIds() in the same function. */
    public const CATEGORY_IDS_ADDED = 'magentoPerfCategoryIdsAdded';

    /** Set on a call that builds a product URL, after addUrlRewrite() in the same function. */
    public const URL_REWRITE_ADDED = 'magentoPerfUrlRewriteAdded';

    /** Set on a foreach over a collection that the same function created. */
    public const CREATED_HERE = 'magentoPerfCollectionCreatedHere';

    /** Uses that need the rows themselves, not just their number. */
    private const ROW_USING_METHODS = ['load', 'getitems', 'getfirstitem', 'getlastitem', 'getcolumnvalues'];

    /** Suffix of the row use key that counts the getFirstItem() calls among them. */
    private const FIRST_ITEM_USES = '#first';

    /** Columns that identify at most one row. */
    private const UNIQUE_COLUMNS = ['entity_id', 'sku', 'increment_id', 'username'];

    /** Methods after which the collection holds its items in memory. */
    private const LOADING_METHODS = ['load', 'getitems', 'count', 'getfirstitem', 'getlastitem'];

    /** @var list<array<string, array<string, true>>> root key => facts, one frame per function */
    private array $frames = [];

    /** @var list<array<string, int>> root key => number of row-using operations, one frame per function */
    private array $rowUses = [];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->frames = [[]];
        $this->rowUses = [$this->countRowUses($nodes)];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FunctionLike && !$node instanceof Expr\ArrowFunction) {
            $this->frames[] = [];
            $this->rowUses[] = $this->countRowUses($node->getStmts() ?? []);

            return null;
        }

        // "$c = <chain>": $c is a new collection; it inherits only what the chain establishes.
        if ($node instanceof Expr\Assign && $node->var instanceof Expr\Variable && is_string($node->var->name)) {
            $key = '$' . $node->var->name;
            // "$c = $c->addFieldToFilter(...)" keeps working on the same collection.
            if ($this->rootKey($node->expr) !== $key) {
                unset($this->frames[count($this->frames) - 1][$key]);
            }
            foreach ([self::LIMITED, self::UNIQUE_FILTER, self::CREATED_HERE] as $fact) {
                if ($this->chainHas($node->expr, $fact)) {
                    $this->record($key, $fact);
                }
            }

            return null;
        }

        // "if ($c->getSize() === 1)": the collection is known to hold one row.
        if ($node instanceof Expr\BinaryOp\Identical || $node instanceof Expr\BinaryOp\NotIdentical
            || $node instanceof Expr\BinaryOp\Equal || $node instanceof Expr\BinaryOp\NotEqual
        ) {
            foreach ([[$node->left, $node->right], [$node->right, $node->left]] as [$call, $value]) {
                if ($call instanceof Expr\MethodCall && $call->name instanceof Node\Identifier
                    && in_array($call->name->toLowerString(), ['getsize', 'gettotalcount'], true)
                    && $value instanceof Node\Scalar\LNumber && $value->value === 1
                ) {
                    $this->record($this->rootKey($call), self::LIMITED);
                }
            }

            return null;
        }

        if ($node instanceof Stmt\Foreach_) {
            $key = $this->rootKey($node->expr);
            if ($this->isTheRootItself($node->expr)) {
                $this->mark($node, $key);
            }
            foreach ([self::LIMITED, self::UNIQUE_FILTER, self::CREATED_HERE] as $fact) {
                if ($this->chainHas($node->expr, $fact)) {
                    $node->setAttribute($fact, true);
                }
            }
            $this->record($key, self::LOADED);
            // The loop assigns a new value to this variable.
            if ($node->valueVar instanceof Expr\Variable && is_string($node->valueVar->name)) {
                unset($this->frames[count($this->frames) - 1]['$' . $node->valueVar->name]);
            }

            return null;
        }

        if ($node instanceof Expr\FuncCall && $node->name instanceof Node\Name
            && in_array($node->name->toLowerString(), ['count', 'sizeof'], true)
        ) {
            $key = $this->rootKey($node->getArgs()[0]->value ?? null);
            $this->mark($node, $key);
            if ($key !== null && ($this->functionRowUses()[$key] ?? 0) > 0) {
                $node->setAttribute(self::ROWS_USED_ELSEWHERE, true);
            }
            $this->record($key, self::LOADED);

            return null;
        }

        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier) {
            return null;
        }
        $name = $node->name->toLowerString();
        $key = $this->rootKey($node->var);

        if ($name === 'addcategoryids') {
            $this->record('*', self::CATEGORY_IDS_ADDED);
        } elseif ($name === 'addurlrewrite') {
            $this->record('*', self::URL_REWRITE_ADDED);
        } elseif (in_array($name, ['getcategoryids', 'getproducturl', 'geturl', 'geturlinstore'], true)) {
            $this->mark($node, '*');
        }

        if ($name === 'getfirstitem') {
            $this->mark($node, $key);
            // Another getFirstItem() needs one row as well; any other use needs the rows anyway.
            $uses = $this->functionRowUses();
            if ($key !== null && ($uses[$key] ?? 0) - ($uses[$key . self::FIRST_ITEM_USES] ?? 0) > 0) {
                $node->setAttribute(self::ROWS_USED_ELSEWHERE, true);
            }
            foreach ([self::LIMITED, self::UNIQUE_FILTER] as $fact) {
                if ($this->chainHas($node->var, $fact)) {
                    $node->setAttribute($fact, true);
                }
            }
        }
        foreach ([self::LIMITED, self::UNIQUE_FILTER] as $fact) {
            if ($this->establishes($node, $fact)) {
                $this->record($key, $fact);
            }
        }
        if (in_array($name, self::LOADING_METHODS, true)) {
            $this->record($key, self::LOADED);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof FunctionLike && !$node instanceof Expr\ArrowFunction) {
            array_pop($this->frames);
            array_pop($this->rowUses);
        }

        return null;
    }

    /**
     * Row uses of the outermost enclosing function, which include those in its closures, so
     * that a use outside a closure is seen from inside it too.
     *
     * @return array<string, int>
     */
    private function functionRowUses(): array
    {
        return $this->rowUses[1] ?? $this->rowUses[0];
    }

    /**
     * Counts, per collection, the operations in these statements that need its rows:
     * iterating it, or load()/getItems()/getFirstItem()/getLastItem(). Nested functions
     * are counted too; they usually run in the same request.
     *
     * @param Node[] $statements
     * @return array<string, int>
     */
    private function countRowUses(array $statements): array
    {
        $uses = [];
        $finder = new NodeFinder();
        foreach ($finder->find($statements, static fn (Node $n): bool => $n instanceof Stmt\Foreach_ || $n instanceof Expr\MethodCall) as $node) {
            $key = null;
            if ($node instanceof Stmt\Foreach_) {
                $key = $this->rootKey($node->expr);
            } elseif ($node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier
                && in_array($node->name->toLowerString(), self::ROW_USING_METHODS, true)
            ) {
                $key = $this->rootKey($node->var);
            }
            if ($key !== null) {
                $uses[$key] = ($uses[$key] ?? 0) + 1;
                if ($node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier && $node->name->toLowerString() === 'getfirstitem') {
                    $uses[$key . self::FIRST_ITEM_USES] = ($uses[$key . self::FIRST_ITEM_USES] ?? 0) + 1;
                }
            }
        }

        return $uses;
    }

    /**
     * Copies the facts already known for $key onto the node.
     */
    private function mark(Node $node, ?string $key): void
    {
        if ($key === null) {
            return;
        }
        // A fact about all items, set in a function, holds in the closures it passes them to.
        foreach ($key === '*' ? $this->frames : [$this->frames[count($this->frames) - 1]] as $frame) {
            foreach ($frame[$key] ?? [] as $fact => $_) {
                $node->setAttribute($fact, true);
            }
        }
    }

    private function record(?string $key, string $fact): void
    {
        if ($key !== null) {
            $this->frames[count($this->frames) - 1][$key][$fact] = true;
        }
    }

    /**
     * What is known about "$order" says nothing about "$order->getItemsCollection()", only about
     * "$collection" and "$collection->getItems()".
     */
    private function isTheRootItself(Expr $expr): bool
    {
        if ($expr instanceof Expr\MethodCall && $expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'getitems') {
            $expr = $expr->var;
        }

        return !$expr instanceof Expr\MethodCall;
    }

    private function chainHas(Expr $expr, string $fact): bool
    {
        while ($expr instanceof Expr\MethodCall) {
            if ($this->establishes($expr, $fact)) {
                return true;
            }
            $expr = $expr->var;
        }

        return false;
    }

    private function establishes(Expr\MethodCall $call, string $fact): bool
    {
        if (!$call->name instanceof Node\Identifier) {
            return false;
        }
        $name = $call->name->toLowerString();

        if ($fact === self::CREATED_HERE) {
            // A method of this class may set a limit of its own; a factory cannot.
            return $name === 'create' && !($call->var instanceof Expr\Variable && $call->var->name === 'this');
        }
        if ($fact === self::LIMITED) {
            // setPageSize(), setPage(), or limit()/limitPage() on getSelect()
            return $name === 'setpagesize' || $name === 'setpage'
                || (($name === 'limit' || $name === 'limitpage')
                    && $call->var instanceof Expr\MethodCall
                    && $call->var->name instanceof Node\Identifier
                    && $call->var->name->toLowerString() === 'getselect');
        }

        if ($name === 'addidfilter' || $name === 'setidfilter') {
            return $this->isEquality($call->getArgs()[0]->value ?? null);
        }
        if ($name !== 'addfieldtofilter' && $name !== 'addattributetofilter') {
            return false;
        }
        $args = $call->getArgs();
        $field = $args[0]->value ?? null;
        if (!$field instanceof Node\Scalar\String_) {
            return false;
        }
        $column = strtolower($field->value);
        $column = str_contains($column, '.') ? substr($column, (int) strrpos($column, '.') + 1) : $column;
        if (!in_array($column, self::UNIQUE_COLUMNS, true)) {
            return false;
        }

        return $this->isEquality($args[1]->value ?? null);
    }

    /**
     * A plain value (not an array literal), or ['eq' => value]. A variable counts as a
     * plain value: if it holds an array at runtime, the rule misses a finding, it does
     * not report a false one.
     */
    private function isEquality(?Expr $condition): bool
    {
        if ($condition === null) {
            return false;
        }
        if (!$condition instanceof Expr\Array_) {
            return true;
        }
        $items = array_values(array_filter(
            $condition->items,
            // @phpstan-ignore notIdentical.alwaysTrue (php-parser 4, bundled with PHPStan 1.12, allows null items)
            static fn ($item): bool => $item !== null,
        ));

        return count($items) === 1
            && $items[0]->key instanceof Node\Scalar\String_
            && strtolower($items[0]->key->value) === 'eq';
    }

    /**
     * Identifies the collection a chain starts from: "$var" or "$this->prop".
     */
    private function rootKey(?Expr $expr): ?string
    {
        while ($expr instanceof Expr\MethodCall) {
            $expr = $expr->var;
        }
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return '$' . $expr->name;
        }
        if ($expr instanceof Expr\PropertyFetch
            && $expr->var instanceof Expr\Variable
            && $expr->var->name === 'this'
            && $expr->name instanceof Node\Identifier
        ) {
            return '$this->' . $expr->name->toString();
        }

        return null;
    }
}
