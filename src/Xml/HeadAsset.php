<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use DOMElement;

/**
 * How core renders an asset element in a layout <head> (magento/magento2@a385d02f).
 */
final class HeadAsset
{
    /** Reader\Head::addContentTypeByNodeName() sets these content types by element name. */
    private const TYPE_BY_TAG = ['css' => 'css', 'script' => 'js', 'font' => 'font'];

    /** Renderer::FONTS_TYPE: these types get rel="preload" as="font" instead of the written attributes. */
    public const FONT_TYPES = ['eot', 'svg', 'ttf', 'woff', 'woff2'];

    /**
     * The content type core gives the element. A remote asset (src_type url or controller) keeps the one the
     * reader sets for <css>, <script> and <font> (it wins over a written one) or the one the layout wrote, and
     * becomes "link" without one (Generator\Head); a local asset gets the
     * extension of its path as written (Asset\Source::getContentType()), so "x.js?v=2" has type "js?v=2".
     */
    public static function contentType(DOMElement $element): string
    {
        if (in_array($element->getAttribute('src_type'), ['url', 'controller'], true)) {
            $written = $element->getAttribute('content_type');

            return self::TYPE_BY_TAG[$element->tagName] ?? ($written !== '' ? $written : 'link');
        }

        return strtolower(pathinfo(trim($element->getAttribute('src')), PATHINFO_EXTENSION));
    }

    /**
     * Whether core wraps the element in an Internet Explorer conditional comment: Renderer::processIeCondition()
     * does so for a non-empty ie_condition.
     */
    public static function hasIeCondition(DOMElement $element): bool
    {
        $condition = $element->getAttribute('ie_condition');

        return $condition !== '' && $condition !== '0';
    }
}
