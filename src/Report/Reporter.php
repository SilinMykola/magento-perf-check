<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

interface Reporter
{
    public function render(AnalysisResult $result): string;
}
