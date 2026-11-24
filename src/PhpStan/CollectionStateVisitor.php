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
    /**
     * Set when the collection is filtered on a fixed unique column or by id, whatever the value.
     * P-COL-04 reads it on a foreach; P-COL-02 reads UNIQUE_FILTER_VALUES instead.
     */
    public const UNIQUE_FILTER = 'magentoPerfCollectionUniqueFilter';

    /**
     * Set on getFirstItem(): the values of the filters on a fixed unique column or by id, for a rule
     * that can tell whether a value may be a list.
     */
    public const UNIQUE_FILTER_VALUES = 'magentoPerfCollectionUniqueFilterValues';

    /**
     * Set on getFirstItem(): the main-table columns the collection is filtered on with an
     * equality, lower case, each with the value it is compared with, for a rule that knows
     * the table's unique keys and can tell whether the value may be a list.
     */
    public const EQUALITY_COLUMNS = 'magentoPerfCollectionEqualityColumns';

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

    /** Prefix of the facts that name a column filtered with an equality, followed by "@" and its branch. */
    private const COLUMN_FACT = 'column:';

    /** Prefix of the facts that hold the value of a filter on a fixed unique column or by id, followed by "@" and its branch. */
    private const UNIQUE_VALUE_FACT = 'unique:';

    /** Fact: the collection joins another table, so a key of its main table no longer identifies one row. */
    private const JOINED = 'joined';

    /** Node attribute: the id of the conditional branch the node opens (a body of if, else, a loop, ?:, ...). */
    private const BRANCH = 'magentoPerfCollectionBranch';

    /** Methods after which the collection holds its items in memory. */
    private const LOADING_METHODS = ['load', 'getitems', 'count', 'getfirstitem', 'getlastitem'];

    /** @var list<array<string, array<string, true|Expr>>> root key => facts (a column fact holds its value), one frame per function */
    private array $frames = [];

    /** @var list<array<string, int>> root key => number of row-using operations, one frame per function */
    private array $rowUses = [];

    /** @var list<int> ids of the conditional branches the current node is in, outermost first */
    private array $branches = [];

    private int $lastBranch = 0;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->frames = [[]];
        $this->rowUses = [$this->countRowUses($nodes)];
        $this->branches = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        $this->markBranches($node);
        $branch = $node->getAttribute(self::BRANCH);
        if (is_int($branch)) {
            $this->branches[] = $branch;
        }

        if ($node instanceof FunctionLike && !$node instanceof Expr\ArrowFunction) {
            $this->frames[] = [];
            $this->rowUses[] = $this->countRowUses($node->getStmts() ?? []);

            return null;
        }

        // "$a = &$b", "$a += 1" and "[$a] = $list" give their variables new values as well.
        if ($node instanceof Expr\AssignRef || $node instanceof Expr\AssignOp
            || ($node instanceof Expr\Assign && ($node->var instanceof Expr\List_ || $node->var instanceof Expr\Array_))
        ) {
            $this->forgetValuesIn($node->var);
        }

        // "$c = <chain>" or "$this->c = <chain>": $c is a new collection; it inherits only what the chain establishes.
        if ($node instanceof Expr\Assign && ($key = $this->rootKey($node->var)) !== null) {
            $this->forgetValuesOf($key);
            // "$c = $c->addFieldToFilter(...)" keeps working on the same collection.
            if ($this->rootKey($node->expr) !== $key) {
                unset($this->frames[count($this->frames) - 1][$key]);
            } elseif ($this->hasGetter($node->expr)) {
                // "$c = $c->getCollection()" is something else, with the same variable name.
                unset($this->frames[count($this->frames) - 1][$key][self::CREATED_HERE]);
            }
            foreach ([self::LIMITED, self::UNIQUE_FILTER, self::CREATED_HERE] as $fact) {
                if ($this->chainHas($node->expr, $fact)) {
                    $this->record($key, $fact);
                }
            }
            foreach ($this->chainColumns($node->expr) as [$column, $value]) {
                $this->recordColumn($key, $column, $value);
            }
            foreach ($this->chainUniqueValues($node->expr) as $value) {
                $this->recordUniqueValue($key, $value);
            }
            if ($this->chainJoins($node->expr)) {
                $this->record($key, self::JOINED);
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
                    // "$this->factory->create()->getSize() === 1" is about a new collection, not $this->factory.
                    if (!$this->callsCreate($call->var)) {
                        $this->record($this->rootKey($call), self::LIMITED);
                    }
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
            // "foreach ($this->factory->create() as ...)" loads a new collection, not $this->factory.
            if (!$this->callsCreate($node->expr)) {
                $this->record($key, self::LOADED);
            }
            // The loop assigns new values to these variables.
            $this->forgetValuesIn($node->keyVar);
            $this->forgetValuesIn($node->valueVar);
            foreach ((new NodeFinder())->findInstanceOf($node->valueVar, Expr\Variable::class) as $variable) {
                if (is_string($variable->name)) {
                    unset($this->frames[count($this->frames) - 1]['$' . $variable->name]);
                }
            }

            return null;
        }

        if ($node instanceof Expr\FuncCall && $node->name instanceof Node\Name
            && in_array($node->name->toLowerString(), ['count', 'sizeof'], true)
        ) {
            $argument = CallArgs::of($node)[0]->value ?? null;
            $key = $this->rootKey($argument);
            $this->mark($node, $key);
            if ($key !== null && ($this->functionRowUses()[$key] ?? 0) > 0) {
                $node->setAttribute(self::ROWS_USED_ELSEWHERE, true);
            }
            // "count($this->factory->create())" loads a new collection, not $this->factory.
            if ($argument === null || !$this->callsCreate($argument)) {
                $this->record($key, self::LOADED);
            }

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
            if ($this->chainHas($node->var, self::LIMITED)) {
                $node->setAttribute(self::LIMITED, true);
            }
            $columns = $this->chainColumns($node->var);
            $uniqueValues = $this->chainUniqueValues($node->var);
            $facts = $key === null ? [] : ($this->frames[count($this->frames) - 1][$key] ?? []);
            foreach ($this->factsInBranch($facts, self::UNIQUE_VALUE_FACT) as [, $value]) {
                $uniqueValues[] = $value;
            }
            $node->setAttribute(self::UNIQUE_FILTER_VALUES, $uniqueValues);
            foreach ($this->factsInBranch($facts, self::COLUMN_FACT) as $column) {
                $columns[] = $column;
            }
            if (isset($facts[self::JOINED]) || $this->chainJoins($node->var)) {
                $columns = [];
            }
            $node->setAttribute(self::EQUALITY_COLUMNS, $columns);
        }
        // "$this->factory->create()->setPageSize(1)" limits a new collection, not $this->factory.
        if ($this->establishes($node, self::LIMITED) && !$this->callsCreate($node->var)) {
            $this->record($key, self::LIMITED);
        }
        if ($this->establishes($node, self::UNIQUE_FILTER)) {
            $this->record($key, self::UNIQUE_FILTER);
        }
        $uniqueValue = $this->uniqueFilterValue($node);
        // "$this->factory->create()->addIdFilter(...)" filters a new collection, not $this->factory.
        if ($uniqueValue !== null && !$this->makesANewObject($node->var)) {
            $this->recordUniqueValue($key, $uniqueValue);
        }
        $column = $this->equalityColumn($node);
        // "$this->factory->create()->addFieldToFilter(...)" filters a new collection, not $this->factory.
        if ($column !== null && !$this->makesANewObject($node->var)) {
            $this->recordColumn($key, ...$column);
        }
        // "$c->getSelect()->joinLeft(...)" joins on $c's query; "$factory->create()->join(...)" on a new one.
        if ($this->joins($node) && !$this->callsCreate($node->var)) {
            $this->record($key, self::JOINED);
        }
        // "$this->factory->create()->getFirstItem()" loads a new collection, not $this->factory.
        if (in_array($name, self::LOADING_METHODS, true) && !$this->callsCreate($node->var)) {
            $this->record($key, self::LOADED);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if (is_int($node->getAttribute(self::BRANCH))) {
            array_pop($this->branches);
        }
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
            // "$this->factory->create()->getItems()" uses the rows of a new collection, not of $this->factory.
            if ($node instanceof Stmt\Foreach_ && !$this->callsCreate($node->expr)) {
                $key = $this->rootKey($node->expr);
            } elseif ($node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier
                && in_array($node->name->toLowerString(), self::ROW_USING_METHODS, true)
                && !$this->callsCreate($node->var)
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
                if (!str_starts_with($fact, self::COLUMN_FACT) && !str_starts_with($fact, self::UNIQUE_VALUE_FACT) && $fact !== self::JOINED) {
                    $node->setAttribute($fact, true);
                }
            }
        }
    }

    private function record(?string $key, string $fact, ?Expr $value = null): void
    {
        if ($key !== null) {
            $this->frames[count($this->frames) - 1][$key][$fact] = $value ?? true;
        }
    }

    private function recordColumn(?string $key, string $column, Expr $value): void
    {
        $this->record($key, self::COLUMN_FACT . $column . '@' . $this->currentBranch(), $value);
    }

    private function recordUniqueValue(?string $key, Expr $value): void
    {
        $this->record($key, self::UNIQUE_VALUE_FACT . spl_object_id($value) . '@' . $this->currentBranch(), $value);
    }

    private function currentBranch(): int
    {
        return $this->branches === [] ? 0 : $this->branches[count($this->branches) - 1];
    }

    /**
     * The facts with this prefix that hold where the current node is, as [name, value]: a filter set in a
     * branch counts only within that branch, since "if ($id) { filter }" may not run.
     *
     * @param array<string, true|Expr> $facts
     * @return list<array{string, Expr}>
     */
    private function factsInBranch(array $facts, string $prefix): array
    {
        $found = [];
        foreach ($facts as $fact => $value) {
            if (!str_starts_with($fact, $prefix) || !$value instanceof Expr) {
                continue;
            }
            [$name, $branch] = explode('@', substr($fact, strlen($prefix)), 2);
            if ($branch === '0' || in_array((int) $branch, $this->branches, true)) {
                $found[] = [$name, $value];
            }
        }

        return $found;
    }

    /**
     * Drops the column and unique filter facts whose value reads $key: the rule checks the value's type where
     * getFirstItem() is called, so after "$ids = 5" a filter on the list "$ids" would look like a filter on one value.
     */
    private function forgetValuesOf(string $key): void
    {
        $frame = &$this->frames[count($this->frames) - 1];
        foreach ($frame as $root => $facts) {
            foreach ($facts as $fact => $value) {
                if ($value instanceof Expr && (new NodeFinder())->findFirst($value, fn (Node $n): bool => $n instanceof Expr && $this->rootKey($n) === $key) !== null) {
                    unset($frame[$root][$fact]);
                }
            }
        }
    }

    /**
     * Drops the filter facts whose value reads a variable that this assignment target writes.
     */
    private function forgetValuesIn(?Expr $target): void
    {
        if ($target instanceof Expr\List_ || $target instanceof Expr\Array_) {
            foreach ($target->items as $item) {
                // A skipped item, as in "[, $b] = ...", is null.
                if ($item !== null) {
                    $this->forgetValuesIn($item->value);
                }
            }

            return;
        }
        // "$map[$id] = ..." writes $map, not $id.
        while ($target instanceof Expr\ArrayDimFetch) {
            $target = $target->var;
        }
        $key = $this->rootKey($target);
        if ($key !== null) {
            $this->forgetValuesOf($key);
        }
    }

    /**
     * Gives each body that runs only on a condition its own branch id: the statements of if, elseif,
     * else, case, catch and loops, the operands of ?: and ?? that may not run, match arms, and the
     * body of an arrow function, which runs only when it is called.
     */
    private function markBranches(Node $node): void
    {
        $bodies = match (true) {
            $node instanceof Stmt\If_, $node instanceof Stmt\ElseIf_, $node instanceof Stmt\Else_,
            $node instanceof Stmt\Case_, $node instanceof Stmt\Catch_, $node instanceof Stmt\Foreach_,
            $node instanceof Stmt\For_, $node instanceof Stmt\While_, $node instanceof Stmt\Do_ => [$node->stmts],
            $node instanceof Expr\Ternary => [[$node->if], [$node->else]],
            $node instanceof Expr\BinaryOp\Coalesce, $node instanceof Expr\BinaryOp\BooleanAnd,
            $node instanceof Expr\BinaryOp\BooleanOr, $node instanceof Expr\BinaryOp\LogicalAnd,
            $node instanceof Expr\BinaryOp\LogicalOr => [[$node->right]],
            $node instanceof Expr\Match_ => array_map(static fn (Node\MatchArm $arm): array => [$arm->body], $node->arms),
            $node instanceof Expr\ArrowFunction => [[$node->expr]],
            default => [],
        };
        foreach ($bodies as $body) {
            $id = ++$this->lastBranch;
            foreach ($body as $child) {
                $child?->setAttribute(self::BRANCH, $id);
            }
        }
    }

    private function callsCreate(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'create') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a call of this chain joins another table: join(), joinLeft(), getSelect()->join(), ...
     */
    private function chainJoins(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($this->joins($expr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * join*(), or from() or union() on getSelect(), which add rows of another table or query.
     */
    private function joins(Expr\MethodCall $call): bool
    {
        if (!$call->name instanceof Node\Identifier) {
            return false;
        }
        $name = $call->name->toLowerString();

        return str_starts_with($name, 'join') || (in_array($name, ['from', 'union'], true) && $this->callsGetSelect($call->var));
    }

    private function callsGetSelect(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'getselect') {
                return true;
            }
        }

        return false;
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
            // A getter after create() hands over something else, such as the items of a new order.
            if ($fact === self::CREATED_HERE && $this->isGetter($expr)) {
                return false;
            }
            $expr = $expr->var;
        }

        return false;
    }

    /**
     * Main-table columns that the calls of this chain filter on with an equality, with their values.
     *
     * @return list<array{string, Expr}>
     */
    private function chainColumns(Expr $expr): array
    {
        $columns = [];
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            $column = $this->equalityColumn($expr);
            if ($column !== null) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Values of the filters on a fixed unique column or by id in the calls of this chain.
     *
     * @return list<Expr>
     */
    private function chainUniqueValues(Expr $expr): array
    {
        $values = [];
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            $value = $this->uniqueFilterValue($expr);
            if ($value !== null) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * The column of addFieldToFilter()/addAttributeToFilter() with an equality condition, lower case, when it
     * has no table alias or the main_table one, with the value it is compared with; else null.
     *
     * @return ?array{string, Expr}
     */
    private function equalityColumn(Expr\MethodCall $call): ?array
    {
        $field = $this->equalityField($call);
        $value = $this->equalityValue(CallArgs::of($call)[1]->value ?? null);
        if ($field === null || $value === null || (str_contains($field, '.') && !str_starts_with($field, 'main_table.'))) {
            return null;
        }

        return [str_starts_with($field, 'main_table.') ? substr($field, strlen('main_table.')) : $field, $value];
    }

    /**
     * The field of addFieldToFilter()/addAttributeToFilter() with an equality condition, lower case and with
     * any table alias, or null.
     */
    private function equalityField(Expr\MethodCall $call): ?string
    {
        if (!$call->name instanceof Node\Identifier
            || !in_array($call->name->toLowerString(), ['addfieldtofilter', 'addattributetofilter'], true)
        ) {
            return null;
        }
        $args = CallArgs::of($call);
        $field = $args[0]->value ?? null;
        if (!$field instanceof Node\Scalar\String_ || $this->equalityValue($args[1]->value ?? null) === null) {
            return null;
        }

        return strtolower($field->value);
    }

    /**
     * Whether the chain calls create() or a getter, which hand over another object than its root.
     */
    private function makesANewObject(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($this->isGetter($expr) || ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'create')) {
                return true;
            }
        }

        return false;
    }

    private function hasGetter(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($this->isGetter($expr)) {
                return true;
            }
        }

        return false;
    }

    private function isGetter(Expr\MethodCall $call): bool
    {
        return $call->name instanceof Node\Identifier
            && str_starts_with($call->name->toLowerString(), 'get')
            && $call->name->toLowerString() !== 'getitems';
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
            // setPageSize(), setPage(), or limit()/limitPage() anywhere after getSelect()
            if ($name === 'setpagesize' || $name === 'setpage') {
                return true;
            }
            if ($name !== 'limit' && $name !== 'limitpage') {
                return false;
            }

            return $this->callsGetSelect($call->var);
        }

        return $this->uniqueFilterValue($call) !== null;
    }

    /**
     * The value of addIdFilter()/setIdFilter(), or of an equality filter on a fixed unique column; else null.
     */
    private function uniqueFilterValue(Expr\MethodCall $call): ?Expr
    {
        if (!$call->name instanceof Node\Identifier) {
            return null;
        }
        $name = $call->name->toLowerString();
        if ($name === 'addidfilter' || $name === 'setidfilter') {
            return $this->equalityValue(CallArgs::of($call)[0]->value ?? null);
        }
        $column = $this->equalityField($call);
        if ($column === null) {
            return null;
        }
        $column = str_contains($column, '.') ? substr($column, (int) strrpos($column, '.') + 1) : $column;

        return in_array($column, self::UNIQUE_COLUMNS, true) ? $this->equalityValue(CallArgs::of($call)[1]->value ?? null) : null;
    }

    /**
     * The value compared with, for a plain value (not an array literal), a single value for ['eq' => value];
     * else null.
     * A variable counts as a plain value: P-COL-02 checks its type where getFirstItem() is called.
     */
    private function equalityValue(?Expr $condition): ?Expr
    {
        if ($condition === null) {
            return null;
        }
        if (!$condition instanceof Expr\Array_) {
            return $condition;
        }
        $items = array_values(array_filter(
            $condition->items,
            // @phpstan-ignore notIdentical.alwaysTrue (php-parser 4, bundled with PHPStan 1.12, allows null items)
            static fn ($item): bool => $item !== null,
        ));

        if (count($items) !== 1
            || !$items[0]->key instanceof Node\Scalar\String_
            || strtolower($items[0]->key->value) !== 'eq'
        ) {
            return null;
        }

        // ['eq' => $list] renders "= 1, 2", not IN (...): it never matches many rows, so stand for one value.
        return new Node\Scalar\String_('eq');
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
