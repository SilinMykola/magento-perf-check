<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Rule;

use MagentoPerfRules\Rule\RuleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Every registered rule has a documentation page with a Basis section, and the
 * page links to Magento core only at a pinned commit (docs/SPEC.md section 2).
 */
final class RuleDocumentationTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    public function testEveryRuleHasADocPageWithBasis(): void
    {
        $problems = [];
        foreach ((new RuleRegistry())->all() as $rule) {
            $path = self::ROOT . '/' . $rule->docPath();
            if (!is_file($path)) {
                $problems[] = $rule->docPath() . ': missing';
                continue;
            }
            $content = (string) file_get_contents($path);
            if (preg_match('/^## Basis\s*$/m', $content) !== 1) {
                $problems[] = $rule->docPath() . ': no "## Basis" section';
            }
            if (preg_match_all('#github\.com/magento/magento2/blob/2\.4-develop/\S+#', $content, $matches) > 0) {
                foreach ($matches[0] as $link) {
                    $problems[] = $rule->docPath() . ': branch link ' . $link;
                }
            }
        }

        self::assertSame([], $problems);
    }
}
