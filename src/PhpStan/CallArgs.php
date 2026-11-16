<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;

/**
 * The arguments of a call. A first-class callable such as strlen(...) or $this->method(...) has none:
 * CallLike::getArgs() asserts against it, and without assertions returns a placeholder that is no Arg.
 */
final class CallArgs
{
    /**
     * @return array<Node\Arg>
     */
    public static function of(CallLike $call): array
    {
        return $call->isFirstClassCallable() ? [] : $call->getArgs();
    }
}
