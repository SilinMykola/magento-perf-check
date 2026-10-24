<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

/**
 * Message prefixes shared by PHPStan rules and the runner. Kept free of PHPStan
 * classes: the runner must not load PHPStan or php-parser in its own process, where
 * they could clash with the php-parser version installed in vendor.
 */
final class NoticePrefix
{
    /** P-COL-02 notice for a getFirstItem() receiver of unknown type. */
    public const UNTYPED_RECEIVER = '[notice:P-COL-02]';
}
