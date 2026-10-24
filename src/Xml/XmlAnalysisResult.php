<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;

final class XmlAnalysisResult
{
    /**
     * @param list<Finding> $findings
     * @param list<Notice> $notices
     */
    public function __construct(
        public readonly array $findings,
        public readonly array $notices,
        public readonly int $filesAnalyzed,
    ) {
    }
}
