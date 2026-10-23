<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ObjectManagerFallbackVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-DI-02: a constructor should only assign its dependencies and validate arguments.
 *
 * @implements Rule<Stmt\ClassMethod>
 */
final class ConstructorWorkRule implements Rule
{
    public const ID = 'P-DI-02';
    public const IDENTIFIER = 'magentoPerf.constructorWork';

    public function getNodeType(): string
    {
        return Stmt\ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name->toLowerString() !== '__construct' || $node->stmts === null || $scope->getClassReflection() === null
            || $this->isOutsideObjectManager($scope->getFile())
        ) {
            return [];
        }
        foreach ($node->stmts as $statement) {
            if (!$this->isAllowed($statement)) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        '[%s] The constructor of %s does work beyond assigning dependencies and validating arguments. '
                        . 'Move it to the method that needs the result, so it does not run every time the object is created.',
                        self::ID,
                        $scope->getClassReflection()->getName(),
                    ))->identifier(self::IDENTIFIER)->line($statement->getStartLine())->build(),
                ];
            }
        }

        return [];
    }

    /** @var array<string, bool> directory => whether code there is outside the object manager */
    private array $outsideByDirectory = [];

    /**
     * Composer plugins and the coding standard are never built by the object manager,
     * so its construction rules do not apply to them.
     */
    private function isOutsideObjectManager(string $file): bool
    {
        if (str_contains($file, '/magento/magento-coding-standard/')) {
            return true;
        }
        $dir = dirname($file);
        $visited = [];
        $outside = false;
        while ($dir !== dirname($dir)) {
            if (isset($this->outsideByDirectory[$dir])) {
                $outside = $this->outsideByDirectory[$dir];
                break;
            }
            $visited[] = $dir;
            if (is_file($dir . '/composer.json')) {
                $data = json_decode((string) @file_get_contents($dir . '/composer.json'), true);
                $outside = is_array($data) && ($data['type'] ?? null) === 'composer-plugin';
                break;
            }
            $dir = dirname($dir);
        }
        foreach ($visited as $visitedDir) {
            $this->outsideByDirectory[$visitedDir] = $outside;
        }

        return $outside;
    }

    private function isAllowed(Stmt $statement): bool
    {
        if ($statement instanceof Stmt\Nop) {
            return true;
        }
        if ($statement instanceof Stmt\If_ || $statement instanceof Stmt\Foreach_) {
            return $this->onlyThrows($statement);
        }
        if (!$statement instanceof Stmt\Expression) {
            return false;
        }
        $expr = $statement->expr;
        if ($expr instanceof Expr\StaticCall
            && $expr->class instanceof Node\Name && $expr->class->toLowerString() === 'parent'
            && $expr->name instanceof Node\Identifier && $expr->name->toLowerString() === '__construct'
        ) {
            return true;
        }

        $target = $expr instanceof Expr\Assign ? $expr->var : null;
        $toProperty = $target instanceof Expr\PropertyFetch
            && $target->var instanceof Expr\Variable && $target->var->name === 'this';

        return $expr instanceof Expr\Assign
            && ($toProperty || $target instanceof Expr\Variable)
            && $this->isPlainValue($expr->expr);
    }

    /**
     * Validation: if/foreach whose bodies contain nothing but nested validation and throws.
     */
    private function onlyThrows(Stmt $statement): bool
    {
        $bodies = [];
        if ($statement instanceof Stmt\If_) {
            $bodies[] = $statement->stmts;
            foreach ($statement->elseifs as $elseif) {
                $bodies[] = $elseif->stmts;
            }
            if ($statement->else !== null) {
                $bodies[] = $statement->else->stmts;
            }
        } elseif ($statement instanceof Stmt\Foreach_) {
            $bodies[] = $statement->stmts;
        }
        foreach ($bodies as $body) {
            foreach ($body as $inner) {
                $allowed = $inner instanceof Stmt\If_ || $inner instanceof Stmt\Foreach_
                    ? $this->onlyThrows($inner)
                    : $this->isThrow($inner);
                if (!$allowed) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * "throw" is a statement in php-parser 4 and an expression in php-parser 5.
     */
    private function isThrow(Stmt $statement): bool
    {
        return str_ends_with($statement::class, '\Throw_')
            || ($statement instanceof Stmt\Expression && $statement->expr instanceof Expr\Throw_);
    }

    /**
     * A parameter, a literal, a constant, or the documented ObjectManager fallback.
     */
    private function isPlainValue(Expr $expr): bool
    {
        if ($expr instanceof Expr\Variable
            || $expr instanceof Node\Scalar
            || $expr instanceof Expr\ConstFetch
            || $expr instanceof Expr\ClassConstFetch
        ) {
            return true;
        }
        if ($expr instanceof Expr\Cast) {
            return $this->isPlainValue($expr->expr);
        }
        if ($expr instanceof Expr\Array_) {
            foreach ($expr->items as $item) {
                // @phpstan-ignore identical.alwaysFalse (php-parser 4, bundled with PHPStan 1.12, allows null items)
                if ($item === null || !$this->isPlainValue($item->value)) {
                    return false;
                }
            }

            return true;
        }
        $fallback = match (true) {
            $expr instanceof Expr\BinaryOp\Coalesce => [$expr->left, $expr->right],
            $expr instanceof Expr\Ternary && $expr->if === null => [$expr->cond, $expr->else],
            default => null,
        };
        if ($fallback === null) {
            return false;
        }
        [$value, $default] = $fallback;
        if (!$this->isPlainValue($value)) {
            return false;
        }
        if ($this->isPlainValue($default) || $default instanceof Expr\New_) {
            return true;
        }
        while ($default instanceof Expr\MethodCall) {
            $default = $default->var;
        }

        return $default instanceof Expr\StaticCall && $default->getAttribute(ObjectManagerFallbackVisitor::FALLBACK) === true;
    }
}
