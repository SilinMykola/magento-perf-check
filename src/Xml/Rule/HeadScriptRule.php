<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-LAY-04: a script added to the page head by layout XML without async or defer.
 */
final class HeadScriptRule implements XmlRule
{
    public const ID = 'X-LAY-04';

    /**
     * The scripts core never defers, from Renderer::DEFAULT_CRITICAL_SCRIPT_PATTERNS (magento/magento2@a385d02f).
     * Core matches them against the asset URL, so the src gets a leading "/" and "::" becomes "/" before matching.
     */
    private const CRITICAL = [
        '/\/require(\.min)?\.js$/',
        '/\/requirejs-config(\.min)?\.js$/',
        '/\/requirejs\/mixins(\.min)?\.js$/',
        '/\/requirejs\/baseUrlResolver(\.min)?\.js$/',
        '/\/requirejs-map(\.min)?\.js$/',
        '#/(?:_cache|merged)/#',
        '#/requirejs-min-resolver(\.min)?(\.[A-Za-z0-9]+)?\.js$#',
        '/\/static(\.min)?\.js$/',
        '#/js/bundle/[^/]+(\.min)?(\.[A-Za-z0-9]+)?\.js$#',
        '/\/sri(\.min)?\.js$/',
    ];

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_LAYOUT
            && in_array($file->area, ['frontend', 'base'], true)
            && $file->handle !== null;
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '//head/script[@src] | //head/link[@src]') as $element) {
            $src = trim($element->getAttribute('src'));
            if (!self::rendersScript($element, $src)) {
                continue;
            }
            if ($element->hasAttribute('async') || $element->hasAttribute('defer') || $element->hasAttribute('ie_condition')) {
                continue;
            }
            $url = '/' . str_replace('::', '/', $src);
            foreach (self::CRITICAL as $pattern) {
                if (preg_match($pattern, $url) === 1) {
                    continue 2;
                }
            }
            $findings[] = new Finding(
                self::ID,
                Severity::Info,
                $file->relativePath,
                $element->getLineNo(),
                sprintf(
                    '[%s] Script "%s" is added to the head of handle "%s" without async or defer: the browser stops '
                    . 'rendering the page until it has loaded and run. Add defer="defer" (or async). Core defers it '
                    . 'only when dev/js/defer_non_critical is on (Magento 2.4.9 and later).',
                    self::ID,
                    $src,
                    (string) $file->handle,
                ),
                $element,
            );
        }

        return $findings;
    }

    /**
     * Whether core renders the element as a script, as Generator\Head and Asset\Source decide: a remote
     * asset (src_type url or controller) by its content type, which the reader sets to "js" for <script>
     * and which a <link> gets only when written; a local asset by the extension of its src as written.
     */
    private static function rendersScript(DOMElement $element, string $src): bool
    {
        if (in_array($element->getAttribute('src_type'), ['url', 'controller'], true)) {
            return $element->tagName === 'script' || $element->getAttribute('content_type') === 'js';
        }

        return strtolower(pathinfo($src, PATHINFO_EXTENSION)) === 'js';
    }
}
