<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

interface Reporter
{
    public function render(AnalysisResult $result): string;
}
