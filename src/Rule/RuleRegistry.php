<?php

declare(strict_types=1);

namespace MagentoPerfRules\Rule;

/**
 * Metadata of every rule, shared by the XML analyzer, the PHPStan extension and the report.
 * Sources come from docs/source-audit.md.
 */
final class RuleRegistry
{
    /**
     * Base for documentation links in findings. Points at this checkout until the
     * repository is published (change m0-skeleton, design D7).
     */
    public const DOCS_BASE = __DIR__ . '/../..';

    private const CORE = 'https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/';

    /** @var array<string, RuleDefinition> */
    private array $rules = [];

    public function __construct()
    {
        foreach (self::definitions() as $rule) {
            $this->rules[$rule->id] = $rule;
        }
    }

    /**
     * @return list<RuleDefinition>
     */
    public function all(): array
    {
        return array_values($this->rules);
    }

    public function find(string $id): ?RuleDefinition
    {
        return $this->rules[$id] ?? null;
    }

    public function docUrl(string $id): string
    {
        return (realpath(self::DOCS_BASE) ?: self::DOCS_BASE) . '/docs/rules/' . $id . '.md';
    }

    /**
     * @return list<RuleDefinition>
     */
    private static function definitions(): array
    {
        return [
            new RuleDefinition(
                'X-LAY-01',
                'Block with cacheable="false" in a frontend layout',
                Severity::Warning,
                RuleStatus::Default,
                [
                    new Source(
                        SourceLevel::A,
                        'https://developer.adobe.com/commerce/php/development/cache/page/',
                        '"If any block in a layout is designated as uncacheable, the entire page is uncacheable."',
                    ),
                    new Source(
                        SourceLevel::A,
                        'https://developer.adobe.com/commerce/php/development/cache/page/public-content',
                        '"Using cacheable="false" inside the default.xml file disables caching for all pages on the site."',
                    ),
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'lib/internal/Magento/Framework/View/Layout.php#L1192-L1210',
                        'Layout::isCacheable() returns false when any <block cacheable="false"> is present in the layout.',
                    ),
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75',
                        'Public cache headers are set only when the layout is cacheable.',
                    ),
                ],
            ),
            new RuleDefinition(
                'P-LOOP-01',
                'Model load() or repository get()/getById() inside a loop',
                Severity::Error,
                RuleStatus::Default,
                [
                    new Source(
                        SourceLevel::A,
                        'https://developer.adobe.com/commerce/php/best-practices/extensions/security',
                        '"Running SQL queries in a loop often results in a performance bottleneck."',
                    ),
                    new Source(
                        SourceLevel::A,
                        'https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review',
                        '"Are queries executed in a loop? This loop can be outside of the edited files."',
                    ),
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568',
                        'AbstractModel::load() delegates to the resource model: one database round trip per call.',
                    ),
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328',
                        'ProductRepository::getById() loads the product unless that id is already cached.',
                    ),
                ],
            ),
            new RuleDefinition(
                'P-COL-02',
                'getFirstItem() on a database collection without setPageSize()',
                Severity::Error,
                RuleStatus::Default,
                [
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'lib/internal/Magento/Framework/Data/Collection.php#L305-L315',
                        'getFirstItem() calls load() and returns the first loaded item; it adds no LIMIT.',
                    ),
                    new Source(
                        SourceLevel::B,
                        self::CORE . 'lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763',
                        '_renderLimit() applies limitPage() only when a page size is set.',
                    ),
                ],
            ),
        ];
    }
}
