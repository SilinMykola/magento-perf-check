<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use Silin\MagentoPerfCheck\Project\ProjectIndex;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;

/**
 * Gives PHP rules the project index, built (or read from cache) on first use from
 * the magentoPerf.projectRoot parameter.
 */
final class ProjectIndexProvider
{
    private ?ProjectIndex $index = null;

    public function __construct(private readonly string $projectRoot)
    {
    }

    public function get(): ProjectIndex
    {
        return $this->index ??= (new ProjectIndexBuilder())->build(rtrim($this->projectRoot, '/'));
    }
}
