<?php

declare(strict_types=1);

namespace LoopContextFixture;

final class Example
{
    /**
     * @param list<int> $ids
     */
    public function run(Repo $repo, array $ids, Collection $collection): void
    {
        foreach ($repo->items() as $item) {
            $repo->inForeach();
        }

        for ($i = $repo->init(); $i < 3; $i++) {
            $repo->inFor();
        }

        while ($ids !== []) {
            $repo->inWhile();
            array_pop($ids);
        }

        do {
            $repo->inDo();
        } while (false);

        array_map(function (int $id) use ($repo): void {
            $repo->inArrayMap();
        }, $ids);

        array_map(fn (int $id) => $repo->inArrowFn(), $ids);

        array_walk($ids, function (int $id) use ($repo): void {
            $repo->inArrayWalk();
        });

        $collection->walk(function () use ($repo): void {
            $repo->inCollectionWalk();
        });

        $stored = function () use ($repo): void {
            $repo->inStoredClosure();
        };
        $stored();

        usort($ids, function (int $a, int $b) use ($repo): int {
            $repo->inUsort();

            return $a <=> $b;
        });

        $repo->outside();
    }
}

interface Repo
{
    /** @return list<int> */
    public function items(): array;

    public function init(): int;

    public function inForeach(): void;

    public function inFor(): void;

    public function inWhile(): void;

    public function inDo(): void;

    public function inArrayMap(): void;

    public function inArrowFn(): void;

    public function inArrayWalk(): void;

    public function inCollectionWalk(): void;

    public function inStoredClosure(): void;

    public function inUsort(): void;

    public function outside(): void;
}

interface Collection
{
    public function walk(callable $callback): void;
}
