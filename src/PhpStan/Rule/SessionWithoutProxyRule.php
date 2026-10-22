<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * P-DI-01: a session (or cookie manager) injected without a Proxy is created with the
 * object; SessionManager's constructor starts the session.
 *
 * @implements Rule<ClassMethod>
 */
final class SessionWithoutProxyRule implements Rule
{
    public const ID = 'P-DI-01';
    public const IDENTIFIER_SESSION = 'magentoPerf.sessionWithoutProxy';
    public const IDENTIFIER_COOKIE = 'magentoPerf.cookieManagerWithoutProxy';

    private const SESSION = 'Magento\Framework\Session\SessionManagerInterface';
    private const COOKIE_MANAGER = 'Magento\Framework\Stdlib\CookieManagerInterface';

    public function __construct(private readonly ProjectIndexProvider $index)
    {
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection();
        if ($node->name->toLowerString() !== '__construct' || $class === null || $class->isAnonymous()) {
            return [];
        }
        $index = $this->index->get();
        $name = $class->getName();
        if ($index->isEmpty()
            || preg_match('/(\\\\Proxy|\\\\Interceptor|Factory)$/', $name) === 1
            || (new ObjectType(self::SESSION))->isSuperTypeOf(new ObjectType($name))->yes()
        ) {
            return [];
        }

        $classes = [$name, ...array_map(static fn ($parent): string => $parent->getName(), $class->getParents())];
        $errors = [];
        foreach ($node->params as $param) {
            if (!$param->var instanceof Node\Expr\Variable || !is_string($param->var->name) || $param->type === null) {
                continue;
            }
            $type = $scope->getFunctionType($param->type, false, false);
            $typeName = $type->getObjectClassNames()[0] ?? null;
            if ($typeName === null || str_ends_with($typeName, '\\Proxy')) {
                continue;
            }
            if ((new ObjectType(self::SESSION))->isSuperTypeOf($type)->yes()) {
                $identifier = self::IDENTIFIER_SESSION;
            } elseif ((new ObjectType(self::COOKIE_MANAGER))->isSuperTypeOf($type)->yes()) {
                $identifier = self::IDENTIFIER_COOKIE;
            } else {
                continue;
            }
            $argument = $param->var->name;
            foreach ($classes as $configured) {
                if ($index->hasProxyArgument($configured, $argument)) {
                    continue 2;
                }
            }
            $consequence = $identifier === self::IDENTIFIER_SESSION
                ? 'it is created together with this class, and a session starts on creation'
                : 'the Technical guidelines (9.10) forbid injecting it into constructors';
            $errors[] = RuleErrorBuilder::message(sprintf(
                '[%s] %s receives $%s (%s) without a Proxy: %s. '
                . 'Configure %s\Proxy for this argument in di.xml; do not type-hint the Proxy.',
                self::ID,
                $name,
                $argument,
                $typeName,
                $consequence,
                $typeName,
            ))->identifier($identifier)->line($param->getStartLine())->build();
        }

        return $errors;
    }
}
