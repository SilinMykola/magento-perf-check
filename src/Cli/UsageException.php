<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Cli;

use RuntimeException;

/**
 * Invalid input or a failure of the tool itself; the command exits with code 2.
 */
final class UsageException extends RuntimeException
{
}
