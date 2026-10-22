<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-02: a block or view model reads customer data from the session; on cacheable
 * pages that data is not there (or ends up in the cached page).
 *
 * @implements Rule<MethodCall>
 */
final class SessionDataInBlockRule implements Rule
{
    public const ID = 'P-FPC-02';
    public const IDENTIFIER = 'magentoPerf.sessionDataInBlock';

    private const METHODS = ['getcustomer', 'getcustomerid', 'getcustomergroupid', 'getquote'];
    private const SESSION = 'Magento\Framework\Session\SessionManagerInterface';
    private const ABSTRACT_BLOCK = 'Magento\Framework\View\Element\AbstractBlock';
    private const VIEW_MODEL = 'Magento\Framework\View\Element\Block\ArgumentInterface';

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
        if (!$node->name instanceof Node\Identifier || !in_array($node->name->toLowerString(), self::METHODS, true)) {
            return [];
        }
        $class = $scope->getClassReflection();
        if (!MagentoTypes::extends($class, self::ABSTRACT_BLOCK) && !MagentoTypes::implements($class, self::VIEW_MODEL)) {
            return [];
        }
        $session = $this->types->subtypeOf($scope->getType($node->var), self::SESSION);
        if ($session === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s reads %s::%s() during rendering. On cacheable pages the session is depersonalized, '
                . 'so this data is missing or cached for everyone; load it through customer data sections.',
                self::ID,
                $class?->getName() ?? 'This class',
                $session,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
