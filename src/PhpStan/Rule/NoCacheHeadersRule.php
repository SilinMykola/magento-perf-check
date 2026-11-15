<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\CallArgs;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;

/**
 * P-FPC-05: no-cache or private headers set where the response could otherwise be cached.
 * One finding per statement, however many headers the statement sets.
 *
 * @implements Rule<Stmt>
 */
final class NoCacheHeadersRule implements Rule
{
    public const ID = 'P-FPC-05';
    public const IDENTIFIER = 'magentoPerf.noCacheHeaders';

    private const ACTION = 'Magento\Framework\App\ActionInterface';
    private const GET_ACTION = 'Magento\Framework\App\Action\HttpGetActionInterface';
    /** A block's setHeader() is DataObject's magic setter of the "header" data key, not a response header. */
    private const BLOCK = 'Magento\Framework\View\Element\BlockInterface';
    private const RESOLVERS = [
        'Magento\Framework\GraphQl\Query\ResolverInterface',
        'Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface',
    ];

    /** Core endpoints that are private by design (class name prefixes). */
    private const PRIVATE_ENDPOINTS = [
        'Magento\Customer\Controller\Section\Load',
        'Magento\PageCache\Controller\Block\Render',
        'Magento\Downloadable\Controller\Download\\',
    ];

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return Stmt::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Stmt\Expression && !$node instanceof Stmt\Return_) {
            return [];
        }
        $call = (new NodeFinder())->findFirst(
            [$node],
            fn (Node $n): bool => $n instanceof MethodCall && $this->setsNoCache($n, $scope),
        );
        if (!$call instanceof MethodCall || !$call->name instanceof Node\Identifier) {
            return [];
        }
        $class = $scope->getClassReflection();
        if ($class === null || !$this->servesCacheableRequests($class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s() in %s makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.',
                self::ID,
                $call->name->toString(),
                $class->getName(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function setsNoCache(MethodCall $call, Scope $scope): bool
    {
        $name = $call->name instanceof Node\Identifier ? $call->name->toLowerString() : '';
        if ($name === 'setnocacheheaders' || $name === 'setprivateheaders') {
            // A controller's own helper with such a name is not the response; what it sets is reported inside it.
            return !($call->var instanceof Node\Expr\Variable && $call->var->name === 'this');
        }
        if ($call->isFirstClassCallable()) {
            return false;
        }
        $args = CallArgs::of($call);
        $header = $args[0]->value ?? null;
        if ($name !== 'setheader'
            || !$header instanceof Node\Scalar\String_
            // Neither the built-in page cache nor the Varnish configuration reads Pragma from a response.
            || strtolower($header->value) !== 'cache-control'
        ) {
            return false;
        }
        if ($this->isBlockOnly($scope->getType($call->var))) {
            return false;
        }
        $value = $args[1]->value ?? null;
        if ($value === null) {
            return true;
        }
        // A computed value may disable caching. Its literal parts tell when they can: a value that says
        // no-cache, no-store or private does, and one built as "public, max-age=%d", whose other parts can
        // only be numbers, asks for caching.
        [$literals, $numbersOnly] = $this->parts($value, $scope);
        $literal = implode(' ', $literals);
        if (preg_match('/no-cache|no-store|private/i', $literal) === 1) {
            return true;
        }

        return !$value instanceof Node\Scalar\String_ && !($numbersOnly && preg_match('/public|max-age/i', $literal) === 1);
    }

    /**
     * Whether every object the receiver can be is a block, as with LayoutInterface::getBlock(), which
     * returns BlockInterface|bool. A part of unknown type may be the response.
     */
    private function isBlockOnly(Type $receiver): bool
    {
        $blocks = 0;
        foreach (TypeUtils::flattenTypes($receiver) as $part) {
            if ($part->isScalar()->yes() || $part->isNull()->yes()) {
                continue;
            }
            $classes = $part->getObjectClassNames();
            if ($classes === [] || array_filter($classes, fn (string $class): bool => !$this->isBlock($class)) !== []) {
                return false;
            }
            ++$blocks;
        }

        return $blocks > 0;
    }

    private function isBlock(string $class): bool
    {
        return $this->reflectionProvider->hasClass($class) && $this->reflectionProvider->getClass($class)->is(self::BLOCK);
    }

    /**
     * The string literals that a header value is built from, by concatenation, interpolation or as the
     * format of sprintf(), and whether every other part can only be a number: an int, or a sprintf()
     * conversion other than %s.
     *
     * @return array{list<string>, bool}
     */
    private function parts(Node\Expr $value, Scope $scope): array
    {
        if ($value instanceof Node\Scalar\String_) {
            return [[$value->value], true];
        }
        if ($value instanceof Node\Expr\BinaryOp\Concat) {
            [$left, $leftNumbers] = $this->parts($value->left, $scope);
            [$right, $rightNumbers] = $this->parts($value->right, $scope);

            return [[...$left, ...$right], $leftNumbers && $rightNumbers];
        }
        if ($value instanceof Node\Expr\FuncCall && $value->name instanceof Node\Name && $value->name->toLowerString() === 'sprintf'
            && !$value->isFirstClassCallable() && (CallArgs::of($value)[0]->value ?? null) instanceof Node\Scalar\String_
        ) {
            $format = CallArgs::of($value)[0]->value->value;
            preg_match_all('/%(?:\d+\$)?[-+ 0]*(?:\'.)?\d*(?:\.\d+)?([a-zA-Z%])/', $format, $conversions);

            return [[$format], !in_array('s', $conversions[1], true)];
        }
        if ($value instanceof Node\Scalar) {
            // "public, max-age={$ttl}": the literal parts of an interpolated string, which php-parser 4 and 5
            // name differently, and its embedded expressions.
            $literals = [];
            $numbers = true;
            foreach ($value->getSubNodeNames() as $name) {
                foreach (is_array($value->$name) ? $value->$name : [] as $part) {
                    // In php-parser 4 a string part is a Scalar too, so it is tested first.
                    if (is_object($part) && property_exists($part, 'value') && is_string($part->value)) {
                        $literals[] = $part->value;
                    } elseif ($part instanceof Node\Expr) {
                        $numbers = $numbers && $scope->getType($part)->isInteger()->yes();
                    }
                }
            }

            return [$literals, $numbers];
        }

        return [[], $scope->getType($value)->isInteger()->yes()];
    }

    private function servesCacheableRequests(ClassReflection $class): bool
    {
        foreach (self::PRIVATE_ENDPOINTS as $prefix) {
            if (str_starts_with($class->getName(), $prefix)) {
                return false;
            }
        }
        foreach (self::RESOLVERS as $resolver) {
            if ($class->implementsInterface($resolver)) {
                return true;
            }
        }
        if (!$class->implementsInterface(self::ACTION) || str_contains($class->getName(), '\\Controller\\Adminhtml\\')) {
            return false;
        }
        if ($class->implementsInterface(self::GET_ACTION)) {
            return true;
        }
        // A controller that declares no HTTP method interface answers every method, GET included.
        foreach (array_keys($class->getInterfaces()) as $interface) {
            if (preg_match('/^Magento\\\\Framework\\\\App\\\\Action\\\\Http\\w+ActionInterface$/', $interface) === 1) {
                return false;
            }
        }

        return true;
    }
}
