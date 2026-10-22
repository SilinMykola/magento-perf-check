<?php

declare(strict_types=1);

namespace MagentoPerfRules\Rule;

enum RuleStatus: string
{
    /** ✅ in the SPEC catalog: enabled by default. */
    case Default = 'default';
    /** ⏳: enabled once calibrated. */
    case AfterCalibration = 'after-calibration';
    /** 🧪: off by default. */
    case Experimental = 'experimental';
}
