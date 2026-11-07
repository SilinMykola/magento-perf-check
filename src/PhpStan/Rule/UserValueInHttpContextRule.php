<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-03: every value of the HTTP context goes into the page cache key, so a value
 * specific to a visitor or a moment leaves one cached page per value.
 *
 * Only expressions that keep the value are followed: a comparison or a boolean cast
 * reduces it to a flag with two values, which is what the context is for.
 *
 * @implements Rule<MethodCall>
 */
final class UserValueInHttpContextRule implements Rule
{
    public const ID = 'P-FPC-03';
    public const IDENTIFIER = 'magentoPerf.httpContextUserValue';

    private const HTTP_CONTEXT = 'Magento\Framework\App\Http\Context';

    /** Getters whose name says that the value belongs to one visitor. */
    private const USER_GETTERS = ['getcustomerid', 'getcustomeremail', 'getemail', 'getsessionid', 'getquoteid'];

    /** Objects whose own id belongs to one visitor. */
    private const USER_OBJECTS = [
        'Magento\Customer\Api\Data\CustomerInterface',
        'Magento\Customer\Model\Customer',
        'Magento\Framework\Session\SessionManagerInterface',
        'Magento\Quote\Api\Data\CartInterface',
    ];

    private const TIME_FUNCTIONS = ['time', 'microtime', 'date', 'gmdate', 'uniqid', 'rand', 'mt_rand', 'random_int'];
    private const VALUE_KEEPING_FUNCTIONS = ['md5', 'sha1', 'hash', 'crc32', 'strval', 'intval', 'sprintf', 'implode'];
    private const USER_NAME = '/^(customer_?id|customer_?email|email|session_?id|quote_?id|cart_?id)$/i';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'setvalue'
            || !MagentoTypes::isObjectOf($scope->getType($node->var), self::HTTP_CONTEXT)
        ) {
            return [];
        }
        $value = $node->getArgs()[1]->value ?? null;
        if ($value === null || !$this->isUserValue($value, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] A value specific to a visitor or a moment is added to the HTTP context: the page cache keeps one copy per value. '
                . 'Use a value shared by many visitors, such as a customer group, and load personal data through customer sections.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function isUserValue(Expr $value, Scope $scope): bool
    {
        return match (true) {
            $value instanceof MethodCall => $this->isUserGetter($value, $scope),
            $value instanceof Expr\FuncCall => $this->isUserFunction($value, $scope),
            $value instanceof Expr\Array_ => $this->holdsUserValue($value->items, $scope),
            $value instanceof Expr\Variable => is_string($value->name) && preg_match(self::USER_NAME, $value->name) === 1,
            $value instanceof Expr\PropertyFetch => $value->name instanceof Node\Identifier && preg_match(self::USER_NAME, $value->name->toString()) === 1,
            $value instanceof Expr\Cast\Int_, $value instanceof Expr\Cast\String_ => $this->isUserValue($value->expr, $scope),
            $value instanceof Expr\BinaryOp\Concat, $value instanceof Expr\BinaryOp\Coalesce => $this->isUserValue($value->left, $scope) || $this->isUserValue($value->right, $scope),
            $value instanceof Expr\Ternary => ($value->if !== null ? $this->isUserValue($value->if, $scope) : $this->isUserValue($value->cond, $scope)) || $this->isUserValue($value->else, $scope),
            default => false,
        };
    }

    private function isUserGetter(MethodCall $call, Scope $scope): bool
    {
        if (!$call->name instanceof Node\Identifier) {
            return false;
        }
        $method = $call->name->toLowerString();
        if (in_array($method, self::USER_GETTERS, true)) {
            return true;
        }
        if ($method !== 'getid' && $method !== 'getentityid') {
            return false;
        }
        foreach (self::USER_OBJECTS as $class) {
            if (MagentoTypes::isObjectOf($scope->getType($call->var), $class)) {
                return true;
            }
        }

        return false;
    }

    private function isUserFunction(Expr\FuncCall $call, Scope $scope): bool
    {
        if (!$call->name instanceof Node\Name) {
            return false;
        }
        $function = strtolower($call->name->getLast());
        if (in_array($function, self::TIME_FUNCTIONS, true)) {
            return true;
        }
        if (!in_array($function, self::VALUE_KEEPING_FUNCTIONS, true)) {
            return false;
        }
        return $this->holdsUserValue($call->getArgs(), $scope);
    }

    /**
     * @param array<object{value: Expr}|null> $holders call arguments or array items; PHP-Parser 4 has null items
     */
    private function holdsUserValue(array $holders, Scope $scope): bool
    {
        foreach ($holders as $holder) {
            if ($holder !== null && $this->isUserValue($holder->value, $scope)) {
                return true;
            }
        }

        return false;
    }
}
