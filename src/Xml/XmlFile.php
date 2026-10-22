<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml;

final class XmlFile
{
    public const KIND_LAYOUT = 'layout';
    public const KIND_CONFIG = 'config';

    /**
     * @param self::KIND_* $kind
     * @param string $area global, frontend, adminhtml, base, graphql, webapi_rest, ...
     * @param ?string $handle layout handle, only for layout files
     */
    public function __construct(
        public readonly string $absolutePath,
        public readonly string $relativePath,
        public readonly string $kind,
        public readonly string $area,
        public readonly ?string $module,
        public readonly ?string $handle,
    ) {
    }
}
