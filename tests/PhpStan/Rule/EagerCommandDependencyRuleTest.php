<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\EagerCommandDependencyRule;

/**
 * @extends RuleTestCase<EagerCommandDependencyRule>
 */
final class EagerCommandDependencyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EagerCommandDependencyRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $class, string $dependencies): string => sprintf(
            '[P-DI-04] %s is created on every bin/magento run, whichever command is asked for, and so are its dependencies %s. '
            . 'Configure a Proxy in di.xml for those that are costly to create.',
            $class,
            $dependencies,
        );

        $this->analyse([__DIR__ . '/../Fixtures/command-and-batch.php'], [
            [$message('Acme\Batch\Sync', '$products'), 16],
            [$message('Acme\Batch\BaseOfVirtual', '$products, $others'), 48],
            [$message('Acme\Batch\NullableButRequired', '$products'), 288],
        ]);
    }
}
