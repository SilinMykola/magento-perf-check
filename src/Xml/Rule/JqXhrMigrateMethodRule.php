<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\Script\ScriptMatchError;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;
use Silin\MagentoPerfCheck\Xml\ScriptRule;
use Silin\MagentoPerfCheck\Xml\XmlFile;

/**
 * U-JS-01: success(), error() or complete() called on the jqXHR of a jQuery Ajax call or of mage/storage.
 * jQuery 3 has none of them; jquery-migrate, which Magento loaded on every page until 2.4.6, added them back.
 */
final class JqXhrMigrateMethodRule implements ScriptRule
{
    public const ID = 'U-JS-01';

    /**
     * The start of a call that returns a jqXHR: $.ajax/get/post/getJSON/getScript, also through jQuery, or
     * get/post/put/delete of mage/storage, which wraps $.ajax and is usually imported as "storage". It must not be a
     * property of another object, such as this.storage.get(). Group 1 is the callee. Only the start is matched, so a
     * call inside the arguments of another one is found too.
     */
    private const AJAX_CALL = '/(?<![\w$.])((?:\$|jQuery)\s*\.\s*(?:ajax|get|post|getJSON|getScript)|storage\s*\.\s*(?:get|post|put|delete))\s*(?=\()/';

    /** The parentheses of a call, at the offset where they open. */
    private const ARGUMENTS = '/\G(\((?:[^()]++|(?1))*+\))/';

    /** One more call of the chain, at the offset where the last one ended: group 1 is the method, group 2 its parentheses. */
    private const CHAINED_CALL = '/\G\s*\.\s*([A-Za-z_$][\w$]*)\s*(\((?:[^()]++|(?2))*+\))/';

    /** The methods jquery-migrate added to a jqXHR, with the method of jQuery 3 that replaces each. */
    private const MIGRATE_METHODS = ['success' => 'done', 'error' => 'fail', 'complete' => 'always'];

    /**
     * Methods that return the same jqXHR, so a chain goes on after them. then(), pipe() and catch() return a new
     * promise, which jquery-migrate never gave these methods, so a chain ends there.
     */
    private const CHAINABLE = ['done', 'fail', 'always', 'progress'];

    public function ruleId(): string
    {
        return self::ID;
    }

    public function areas(): array
    {
        return ['frontend', 'base', 'adminhtml'];
    }

    public function check(XmlFile $file, ScriptSource $script): array
    {
        // A copy of jQuery itself, as some modules ship, has its own jqXHR: before jQuery 3 it has these methods.
        foreach ($script->code() as [$code]) {
            if (preg_match('/\bjQuery\.fn\s*=\s*jQuery\.prototype\b/', $code) === 1) {
                return [];
            }
        }
        $findings = [];
        $error = null;
        foreach ($script->code() as [$code, $firstLine]) {
            if (preg_match_all(self::AJAX_CALL, $code, $calls, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false) {
                $error = preg_last_error_msg();
                continue;
            }
            foreach ($calls as $call) {
                $at = $call[0][1] + strlen($call[0][0]);
                $matched = preg_match(self::ARGUMENTS, $code, $arguments, 0, $at);
                if ($matched === 1) {
                    $at += strlen($arguments[0]);
                }
                while ($matched === 1 && ($matched = preg_match(self::CHAINED_CALL, $code, $chained, PREG_OFFSET_CAPTURE, $at)) === 1) {
                    $method = $chained[1][0];
                    if (isset(self::MIGRATE_METHODS[$method])) {
                        $findings[] = new Finding(
                            self::ID,
                            Severity::Error,
                            $file->relativePath,
                            $firstLine + substr_count($code, "\n", 0, $chained[1][1]),
                            sprintf(
                                '[%s] .%s() on the result of %s(): jQuery 3 has no such method, and Magento 2.4.6 no longer loads '
                                . 'jquery-migrate, which added it back, so from 2.4.6 this line throws "%s is not a function". Use .%s().',
                                self::ID,
                                $method,
                                (string) preg_replace('/\s+/', '', $call[1][0]),
                                $method,
                                self::MIGRATE_METHODS[$method],
                            ),
                        );
                    } elseif (!in_array($method, self::CHAINABLE, true)) {
                        break;
                    }
                    $at = $chained[0][1] + strlen($chained[0][0]);
                }
                if ($matched === false) {
                    $error = preg_last_error_msg();
                }
            }
        }
        if ($error !== null) {
            throw ScriptMatchError::pcre(self::ID, $error, $findings);
        }

        return $findings;
    }
}
