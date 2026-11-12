<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\HeadAsset;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-LAY-05: a module adds a stylesheet to the head of every storefront page through layout XML.
 */
final class GlobalStylesheetRule implements XmlRule
{
    public const ID = 'X-LAY-05';

    /** Handles that apply to every page; a Hyvä theme adds hyva_<handle> next to each of them. */
    private const EVERY_PAGE = ['default', 'default_head_blocks', 'hyva_default', 'hyva_default_head_blocks'];

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        // Module layouts only: a theme's own stylesheet belongs on every page.
        return $file->kind === XmlFile::KIND_LAYOUT
            && in_array($file->area, ['frontend', 'base'], true)
            && in_array($file->handle, self::EVERY_PAGE, true)
            && preg_match(FileDiscovery::MODULE_LAYOUT, $file->absolutePath) === 1;
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '//head/css[@src] | //head/link[@src]') as $element) {
            $src = trim($element->getAttribute('src'));
            if (!self::rendersStylesheet($element)) {
                continue;
            }
            if (strtolower(trim($element->getAttribute('media'))) === 'print' || HeadAsset::hasIeCondition($element)) {
                continue;
            }
            $findings[] = new Finding(
                self::ID,
                Severity::Warning,
                $file->relativePath,
                $element->getLineNo(),
                sprintf(
                    '[%s] Stylesheet "%s" is added to every storefront page by handle "%s" of a module: the browser '
                    . 'does not show any page until it has loaded. Add it only on the handles of the pages that use '
                    . 'it, or build the styles into the theme\'s stylesheet. Core loads all stylesheets without '
                    . 'blocking only when dev/css/use_css_critical_path is on.',
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
     * Whether core renders the element as <link rel="stylesheet">: a "css" asset always (Renderer::addDefaultAttributes()
     * puts rel="stylesheet" first); any other asset that is neither a script nor a font when the layout writes
     * rel="stylesheet", because core passes the written attributes to its <link>.
     */
    private static function rendersStylesheet(DOMElement $element): bool
    {
        $type = HeadAsset::contentType($element);
        if ($type === 'css') {
            return true;
        }

        return $type !== 'js'
            && !in_array($type, HeadAsset::FONT_TYPES, true)
            && strtolower(trim($element->getAttribute('rel'))) === 'stylesheet';
    }
}
