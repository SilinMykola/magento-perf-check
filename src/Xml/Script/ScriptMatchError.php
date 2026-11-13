<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

use RuntimeException;
use Silin\MagentoPerfCheck\Report\Finding;

/**
 * A script rule could not match its patterns against part of a script (a PCRE limit), so some of that rule's
 * findings in the script may be missing. It carries the findings the rule did make; the analyzer keeps them and
 * reports a notice instead of a silent gap.
 */
final class ScriptMatchError extends RuntimeException
{
    /**
     * @param list<Finding> $findings
     */
    public function __construct(string $message, public readonly array $findings)
    {
        parent::__construct($message);
    }

    /**
     * @param list<Finding> $findings
     */
    public static function pcre(string $ruleId, string $error, array $findings): self
    {
        return new self(sprintf('%s could not read all of this script (%s); some of its findings of this rule may be missing', $ruleId, $error), $findings);
    }
}
