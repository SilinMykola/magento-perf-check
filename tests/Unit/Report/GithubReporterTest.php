<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Report;

use MagentoPerfRules\Report\AnalysisResult;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Report\GithubReporter;
use MagentoPerfRules\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class GithubReporterTest extends TestCase
{
    public function testOneAnnotationPerFindingWithEscaping(): void
    {
        $result = new AnalysisResult(
            [
                new Finding('X-LAY-01', Severity::Error, 'app/code/A/view/frontend/layout/default.xml', 5, "[X-LAY-01] 100% uncached\nsecond line"),
                new Finding('P-LOOP-03', Severity::Warning, 'app/code/A/Model/Weird,Name:x.php', 7, '[P-LOOP-03] save'),
                new Finding('P-COL-03', Severity::Info, 'app/code/A/Model/B.php', 0, '[P-COL-03] info'),
            ],
            [],
            '/srv/shop',
            ['app/code'],
            1.0,
        );

        self::assertSame(
            "::notice file=app/code/A/Model/B.php,title=P-COL-03::[P-COL-03] info\n"
            . "::warning file=app/code/A/Model/Weird%2CName%3Ax.php,line=7,title=P-LOOP-03::[P-LOOP-03] save\n"
            . "::error file=app/code/A/view/frontend/layout/default.xml,line=5,title=X-LAY-01::[X-LAY-01] 100%25 uncached%0Asecond line\n",
            (new GithubReporter())->render($result),
        );
    }
}
