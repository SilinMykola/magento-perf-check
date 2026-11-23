<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Project\MagentoOneCode;

final class MagentoOneCodeTest extends TestCase
{
    public function testVendorNamedLikeACodePoolInAnotherCaseIsMagentoTwo(): void
    {
        $project = sys_get_temp_dir() . '/mpr-m1-' . bin2hex(random_bytes(4));
        mkdir($project . '/app/code/Local/Shop/etc', 0777, true);
        mkdir($project . '/app/code/community/Old', 0777, true);
        try {
            self::assertSame([$project . '/app/code/community'], MagentoOneCode::dirs($project));
        } finally {
            exec('rm -rf ' . escapeshellarg($project));
        }
    }
}
