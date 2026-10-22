<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Test double: reports what the project index knows, once per class.
 *
 * @implements Rule<Class_>
 */
final class IndexReportingRule implements Rule
{
    public function __construct(private readonly ProjectIndexProvider $provider)
    {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $index = $this->provider->get();

        return [
            RuleErrorBuilder::message(sprintf(
                'modules: %d, banner proxy: %s',
                $index->moduleCount,
                $index->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession') ? 'yes' : 'no',
            ))->identifier('magentoPerfTest.index')->build(),
        ];
    }
}
