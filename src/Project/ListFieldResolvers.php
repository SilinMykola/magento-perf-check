<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\Parser;
use Silin\MagentoPerfCheck\Xml\SchemaPreprocessor;

/**
 * Finds the resolvers that run once per list element: those of fields on types reachable
 * from Query or Mutation through a list. Modules extend each other's types, so this needs
 * the schemas of the whole project. A schema that does not parse is left out here; the
 * XML analyzer reports it when it is within the analyzed paths.
 */
final class ListFieldResolvers
{
    /**
     * @param list<string> $schemaFiles
     * @return array<string, string> normalized resolver class => "Type.field"
     */
    public static function fromSchemas(array $schemaFiles): array
    {
        /** @var array<string, list<array{string, bool}>> $fields type => [target type, through a list] */
        $fields = [];
        /** @var array<string, array<string, true>> $members interface or union => its types */
        $members = [];
        /** @var array<string, array<string, true>> $interfaces type => its interfaces */
        $interfaces = [];
        /** @var list<array{string, string, string}> $resolvers [type, field, class] */
        $resolvers = [];

        foreach ($schemaFiles as $file) {
            try {
                $schema = Parser::parse(SchemaPreprocessor::normalize((string) @file_get_contents($file)));
            } catch (SyntaxError) {
                continue;
            }
            foreach ($schema->definitions as $definition) {
                if ($definition instanceof UnionTypeDefinitionNode || $definition instanceof UnionTypeExtensionNode) {
                    foreach ($definition->types as $type) {
                        $members[$definition->name->value][$type->name->value] = true;
                    }
                    continue;
                }
                if (!$definition instanceof ObjectTypeDefinitionNode && !$definition instanceof ObjectTypeExtensionNode
                    && !$definition instanceof InterfaceTypeDefinitionNode && !$definition instanceof InterfaceTypeExtensionNode
                ) {
                    continue;
                }
                $name = $definition->name->value;
                foreach ($definition->interfaces as $interface) {
                    $members[$interface->name->value][$name] = true;
                    $interfaces[$name][$interface->name->value] = true;
                }
                foreach ($definition->fields as $field) {
                    $fields[$name][] = self::target($field);
                    $class = self::resolverClass($field);
                    if ($class !== null) {
                        $resolvers[] = [$name, $field->name->value, $class];
                    }
                }
            }
        }

        $inList = self::typesInLists($fields, $members, $interfaces);
        $result = [];
        foreach ($resolvers as [$type, $field, $class]) {
            // Magento copies the fields of an interface to its types, so a list of a type counts for the interface too.
            if (isset($inList[$type]) || array_intersect_key($members[$type] ?? [], $inList) !== []) {
                $result[ProjectIndex::normalizeClass($class)] ??= $type . '.' . $field;
            }
        }

        return $result;
    }

    /**
     * @return array{string, bool} the named type of the field, and whether a list wraps it
     */
    private static function target(FieldDefinitionNode $field): array
    {
        $type = $field->type;
        $list = false;
        while (!$type instanceof NamedTypeNode) {
            $list = $list || $type instanceof ListTypeNode;
            /** @var ListTypeNode|NonNullTypeNode $type */
            $type = $type->type;
        }

        return [$type->name->value, $list];
    }

    private static function resolverClass(FieldDefinitionNode $field): ?string
    {
        foreach ($field->directives as $directive) {
            if ($directive->name->value !== 'resolver') {
                continue;
            }
            foreach ($directive->arguments as $argument) {
                if ($argument->name->value === 'class' && $argument->value instanceof StringValueNode) {
                    return $argument->value->value;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, list<array{string, bool}>> $fields
     * @param array<string, array<string, true>> $members
     * @param array<string, array<string, true>> $interfaces
     * @return array<string, true>
     */
    private static function typesInLists(array $fields, array $members, array $interfaces): array
    {
        $inList = [];
        $visited = [];
        $queue = [['Query', false], ['Mutation', false]];
        while ($queue !== []) {
            [$type, $listed] = array_pop($queue);
            if (isset($visited[$type][(int) $listed])) {
                continue;
            }
            $visited[$type][(int) $listed] = true;
            if ($listed) {
                $inList[$type] = true;
            }
            foreach (array_keys($members[$type] ?? []) as $member) {
                $queue[] = [$member, $listed];
            }
            // Magento copies the fields of an interface to its types. The interface itself is not
            // queued: that would put its other types in the list too.
            foreach ([$type, ...array_keys($interfaces[$type] ?? [])] as $owner) {
                foreach ($fields[$owner] ?? [] as [$target, $list]) {
                    $queue[] = [$target, $listed || $list];
                }
            }
        }

        return $inList;
    }
}
