<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use DOMElement;
use MagentoPerfRules\Rule\Severity;

final class Finding
{
    /**
     * @param string $file path relative to the analyzed project
     * @param ?DOMElement $element the XML element that triggered an XML finding, used for suppression comments
     */
    public function __construct(
        public readonly string $ruleId,
        public readonly Severity $severity,
        public readonly string $file,
        public readonly int $line,
        public readonly string $message,
        public readonly ?DOMElement $element = null,
    ) {
    }

    public function withSeverity(Severity $severity): self
    {
        return new self($this->ruleId, $severity, $this->file, $this->line, $this->message, $this->element);
    }
}
