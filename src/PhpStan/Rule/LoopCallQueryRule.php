<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Silin\MagentoPerfCheck\PhpStan\LoopCallCollector;
use Silin\MagentoPerfCheck\PhpStan\MemoizingMethodCollector;
use Silin\MagentoPerfCheck\PhpStan\QueryCallCollector;

/**
 * P-LOOP-12: a method called in a loop that runs a query on every call, one level deep.
 * The called method must be declared in the analyzed code and query itself. A query whose result
 * the method keeps (MemoizingMethodCollector) counts only when the loop may call a new object each time.
 *
 * @implements Rule<CollectedDataNode>
 */
final class LoopCallQueryRule implements Rule
{
    public const ID = 'P-LOOP-12';
    public const IDENTIFIER = 'magentoPerf.loopCallQuery';

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Lines of each method whose result is kept for later calls.
        $kept = [];
        foreach ($node->get(MemoizingMethodCollector::class) as $methods) {
            foreach ($methods as $method) {
                $kept[self::key($method['class'], $method['method'])] = $method['kept'];
            }
        }
        $queries = [];
        foreach ($node->get(QueryCallCollector::class) as $calls) {
            foreach ($calls as $call) {
                $queries[self::key($call['class'], $call['method'])][] = $call;
            }
        }

        $errors = [];
        foreach ($node->get(LoopCallCollector::class) as $file => $calls) {
            foreach ($calls as $call) {
                $key = self::key($call['class'], $call['method']);
                // The first query by line; a getList() counts only in a loop over items, as for P-LOOP-09.
                // A kept result spares its query only when the loop calls the same object every time.
                $query = null;
                foreach ($queries[$key] ?? [] as $candidate) {
                    if (($call['item'] || !$candidate['list'])
                        && ($call['perItem'] || !self::inside($candidate['line'], $kept[$key] ?? []))
                        && ($query === null || $candidate['line'] < $query['line'])
                    ) {
                        $query = $candidate;
                    }
                }
                if ($query === null) {
                    continue;
                }
                $errors[] = RuleErrorBuilder::message(sprintf(
                    '[%s] %s::%s() is called inside a loop and runs a query on every call: it calls %s on line %d of %s. '
                    . 'Fetch the data for all items before the loop, with a collection or getList(), and pass it in.',
                    self::ID,
                    $query['class'],
                    $query['method'],
                    $query['query'],
                    $query['line'],
                    $query['file'],
                ))->identifier(self::IDENTIFIER)->file($file)->line($call['line'])->build();
            }
        }

        return $errors;
    }

    /**
     * @param list<array{int, int}> $ranges
     */
    private static function inside(int $line, array $ranges): bool
    {
        foreach ($ranges as [$from, $to]) {
            if ($line >= $from && $line <= $to) {
                return true;
            }
        }

        return false;
    }

    private static function key(string $class, string $method): string
    {
        return $class . '::' . $method;
    }
}
