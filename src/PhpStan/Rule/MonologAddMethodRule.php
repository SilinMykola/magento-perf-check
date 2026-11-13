<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeCombinator;

/**
 * U-LOG-01: addError(), addInfo() and the other add* methods, or emerg(), crit(), err() and warn(), called on a
 * Monolog logger. Monolog 2, which Magento requires from 2.4.4, removed them, so from 2.4.4 such a call throws
 * "Call to undefined method", usually inside the catch block that was meant to log a failure.
 *
 * Only a receiver that is Monolog\Logger or a class extending it is reported, also through parent::, self:: or
 * static:: inside such a class, as long as the method there is
 * Monolog's own or missing: a logger that declares such a method itself keeps it. On Psr\Log\LoggerInterface
 * the call fails from 2.4.2, where Magento resolves the interface to LoggerProxy; that is another change, not
 * reported.
 *
 * @implements Rule<CallLike>
 */
final class MonologAddMethodRule implements Rule
{
    public const ID = 'U-LOG-01';
    public const IDENTIFIER = 'magentoPerf.monologAddMethod';

    private const LOGGER = 'Monolog\Logger';

    /** The Monolog 1 methods that Monolog 2 removed, with the PSR-3 method that replaces each. */
    private const REMOVED = [
        'adddebug' => 'debug',
        'addinfo' => 'info',
        'addnotice' => 'notice',
        'addwarning' => 'warning',
        'adderror' => 'error',
        'addcritical' => 'critical',
        'addalert' => 'alert',
        'addemergency' => 'emergency',
        'warn' => 'warning',
        'err' => 'error',
        'crit' => 'critical',
        'emerg' => 'emergency',
    ];

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ((!$node instanceof MethodCall && !$node instanceof StaticCall)
            || !$node->name instanceof Identifier
            || !isset(self::REMOVED[$node->name->toLowerString()])
        ) {
            return [];
        }
        $method = $node->name->toString();
        foreach ($this->receivers($node, $scope) as $class) {
            if ($class->getAncestorWithClassName(self::LOGGER) === null) {
                continue;
            }
            // A logger that declares the method itself keeps it in Monolog 2.
            if ($class->hasMethod($method) && strcasecmp($class->getMethod($method, $scope)->getDeclaringClass()->getName(), self::LOGGER) !== 0) {
                continue;
            }

            return [
                RuleErrorBuilder::message(sprintf(
                    '[%s] %s() on %s: Monolog 2, which Magento requires from 2.4.4, removed this Monolog 1 method, so from 2.4.4 '
                    . 'the call throws "Call to undefined method". Use %s(), with the same arguments.',
                    self::ID,
                    $method,
                    $class->getName(),
                    self::REMOVED[$node->name->toLowerString()],
                ))->identifier(self::IDENTIFIER)->build(),
            ];
        }

        return [];
    }

    /**
     * The classes a call goes to: the receiver's type for $object->method(), the current class or its parent for
     * self::, static:: and parent::. A call on a class name is a static call of an instance method, which PHPStan
     * reports itself.
     *
     * @return list<ClassReflection>
     */
    private function receivers(MethodCall|StaticCall $node, Scope $scope): array
    {
        if ($node instanceof MethodCall) {
            // A nullable logger fails the same way when it is set.
            return TypeCombinator::removeNull($scope->getType($node->var))->getObjectClassReflections();
        }
        $current = $scope->getClassReflection();
        if (!$node->class instanceof Name || $current === null) {
            return [];
        }
        $class = match ($node->class->toLowerString()) {
            'self', 'static' => $current,
            'parent' => $current->getParentClass(),
            default => null,
        };

        return $class === null ? [] : [$class];
    }
}
