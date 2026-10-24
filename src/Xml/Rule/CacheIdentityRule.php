<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\GraphQlRule;
use Silin\MagentoPerfCheck\Xml\XmlFile;

/**
 * X-GQL-01: a Query field with @cache but neither cacheIdentity nor cacheable: false
 * makes the whole response uncacheable.
 */
final class CacheIdentityRule implements GraphQlRule
{
    public const ID = 'X-GQL-01';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function check(XmlFile $file, DocumentNode $schema): array
    {
        $findings = [];
        foreach ($schema->definitions as $definition) {
            if ((!$definition instanceof ObjectTypeDefinitionNode && !$definition instanceof ObjectTypeExtensionNode)
                || $definition->name->value !== 'Query'
            ) {
                continue;
            }
            foreach ($definition->fields as $field) {
                foreach ($field->directives as $directive) {
                    if ($directive->name->value !== 'cache' || $this->isConfigured($directive->arguments)) {
                        continue;
                    }
                    $findings[] = new Finding(
                        self::ID,
                        Severity::Error,
                        $file->relativePath,
                        $field->loc?->startToken !== null ? $field->loc->startToken->line : 0,
                        sprintf(
                            '[%s] Query field "%s" has @cache without cacheIdentity: every response that contains it becomes uncacheable. '
                            . 'Add cacheIdentity, or cacheable: false if the field must not be cached.',
                            self::ID,
                            $field->name->value,
                        ),
                    );
                }
            }
        }

        return $findings;
    }

    /**
     * @param iterable<\GraphQL\Language\AST\ArgumentNode> $arguments
     */
    private function isConfigured(iterable $arguments): bool
    {
        foreach ($arguments as $argument) {
            if ($argument->name->value === 'cacheIdentity') {
                return true;
            }
            if ($argument->name->value === 'cacheable' && $argument->value instanceof BooleanValueNode && !$argument->value->value) {
                return true;
            }
        }

        return false;
    }
}
