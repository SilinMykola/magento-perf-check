<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * X-PLG-02: a plugin on these methods of the base classes runs for every rendered block or
 * layout element of every page, whatever the plugin is really interested in.
 *
 * @implements Rule<Stmt\ClassMethod>
 */
final class HotRenderPluginRule implements Rule
{
    public const ID = 'X-PLG-02';
    public const IDENTIFIER = 'magentoPerf.hotRenderPlugin';

    /** Normalized class => its methods that run per block or element. */
    private const HOT_METHODS = [
        'magento\framework\view\element\abstractblock' => ['tohtml', 'getchildhtml'],
        'magento\framework\view\element\template' => ['tohtml', 'getchildhtml', 'fetchview'],
        'magento\framework\view\layout' => ['renderelement'],
        'magento\framework\view\layoutinterface' => ['renderelement'],
    ];

    public function __construct(private readonly ProjectIndexProvider $index)
    {
    }

    public function getNodeType(): string
    {
        return Stmt\ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection();
        if ($class === null || !$node->isPublic() || preg_match('/^(?:before|after|around)(.+)$/i', $node->name->toString(), $m) !== 1) {
            return [];
        }
        $method = strtolower($m[1]);
        foreach ($this->index->get()->pluginTargetsOf($class->getName()) as $target) {
            if (in_array($method, self::HOT_METHODS[$target] ?? [], true)) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        '[%s] %s::%s() is a plugin on %s, so it runs for every rendered block or layout element of every page. '
                        . 'Put it on the specific class it is meant for, or keep it very cheap.',
                        self::ID,
                        $class->getName(),
                        $node->name->toString(),
                        $target,
                    ))->identifier(self::IDENTIFIER)->build(),
                ];
            }
        }

        return [];
    }
}
