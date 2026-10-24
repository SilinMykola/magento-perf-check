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

    /** Set on count() when the same function uses the collection's rows elsewhere. */
    public const ROWS_USED_ELSEWHERE = 'magentoPerfCollectionRowsUsedElsewhere';

    /** Uses that need the rows themselves, not just their number. */
    private const ROW_USING_METHODS = ['load', 'getitems', 'getfirstitem', 'getlastitem'];

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
        if ($node instanceof Expr\Assign && $node->var instanceof Expr\Variable && is_string($node->var->name)
            && $node->expr instanceof Expr\MethodCall
        ) {
            $key = '$' . $node->var->name;
            // "$c = $c->addFieldToFilter(...)" keeps working on the same collection.
            if ($this->rootKey($node->expr) !== $key) {
                unset($this->frames[count($this->frames) - 1][$key]);
            }
            foreach ([self::LIMITED, self::UNIQUE_FILTER] as $fact) {
                if ($this->chainHas($node->expr, $fact)) {
                    $this->record($key, $fact);
                }
            }

            return null;
        }

        if ($node instanceof Stmt\Foreach_) {
            $this->record($this->rootKey($node->expr), self::LOADED);

            return null;
        }

        if ($node instanceof Expr\FuncCall && $node->name instanceof Node\Name
            && in_array($node->name->toLowerString(), ['count', 'sizeof'], true)
        ) {
            $key = $this->rootKey($node->getArgs()[0]->value ?? null);
            $this->mark($node, $key);
            if ($key !== null && ($this->rowUses[count($this->rowUses) - 1][$key] ?? 0) > 0) {
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

        if ($name === 'getfirstitem') {
            $this->mark($node, $key);
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
        foreach ($this->frames[count($this->frames) - 1][$key] ?? [] as $fact => $_) {
            $node->setAttribute($fact, true);
        }
    }

    private function record(?string $key, string $fact): void
    {
        if ($key !== null) {
            $this->frames[count($this->frames) - 1][$key][$fact] = true;
        }
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

        if ($fact === self::LIMITED) {
            // setPageSize(), or limit()/limitPage() on getSelect()
            return $name === 'setpagesize'
                || (($name === 'limit' || $name === 'limitpage')
                    && $call->var instanceof Expr\MethodCall
                    && $call->var->name instanceof Node\Identifier
                    && $call->var->name->toLowerString() === 'getselect');
        }

        if ($name === 'addidfilter') {
            return true;
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
