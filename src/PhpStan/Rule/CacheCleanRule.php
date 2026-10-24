<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-09: cleaning the whole full page cache, or every cache frontend, from code.
 *
 * @implements Rule<MethodCall>
 */
final class CacheCleanRule implements Rule
{
    public const ID = 'P-FPC-09';
    public const IDENTIFIER = 'magentoPerf.cacheClean';

    private const TYPE_LIST = 'Magento\Framework\App\Cache\TypeListInterface';
    private const CACHE = 'Magento\Framework\App\CacheInterface';
    private const CLI_COMMAND = 'Symfony\Component\Console\Command\Command';
    private const CONFIG_IMPORTER = 'Magento\Framework\App\DeploymentConfig\ImporterInterface';
    private const CONFIG_VALUE = 'Magento\Framework\App\Config\Value';

    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || $this->isFlushContext($scope)) {
            return [];
        }
        $method = $node->name->toLowerString();
        $type = $scope->getType($node->var);
        $args = $node->getArgs();

        if ($method === 'cleantype' && $this->types->subtypeOf($type, self::TYPE_LIST) !== null && isset($args[0])) {
            $values = array_map(static fn ($string): string => $string->getValue(), $scope->getType($args[0]->value)->getConstantStrings());
            if ($values === ['full_page']) {
                return [$this->error('cleanType(\'full_page\') empties the whole full page cache (with Varnish: a full purge). Clean the cache tags of the changed entities instead.')];
            }
        }
        if ($method === 'clean' && $this->types->subtypeOf($type, self::CACHE) !== null) {
            $tags = $args[0]->value ?? null;
            if ($tags === null || ($tags instanceof Node\Expr\Array_ && $tags->items === [])) {
                return [$this->error('clean() without tags cleans every cache frontend. Pass the cache tags of the changed entities.')];
            }
        }

        return [];
    }

    /**
     * Places where flushing everything is the intended action: CLI commands, setup code,
     * deployment config importers and configuration backend models.
     */
    private function isFlushContext(Scope $scope): bool
    {
        $class = $scope->getClassReflection();
        if (str_contains($scope->getFile(), '/setup/')) {
            return true;
        }
        if ($class === null) {
            return false;
        }

        return preg_match('/\\\\Setup(\\\\|$)/', $class->getName()) === 1
            || MagentoTypes::extends($class, self::CLI_COMMAND)
            || MagentoTypes::implements($class, self::CONFIG_IMPORTER)
            || MagentoTypes::extends($class, self::CONFIG_VALUE);
    }

    private function error(string $text): \PHPStan\Rules\IdentifierRuleError
    {
        return RuleErrorBuilder::message('[' . self::ID . '] ' . $text)->identifier(self::IDENTIFIER)->build();
    }
}
