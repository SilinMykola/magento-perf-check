<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

use MagentoPerfRules\Project\ProjectIndex;
use MagentoPerfRules\Project\ProjectIndexBuilder;

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
