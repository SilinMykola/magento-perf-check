# 6. GraphQL schemas are parsed with webonyx/graphql-php

- Status: Accepted
- Date: 2026-10-22

## Context

X-GQL-01 inspects `Query` fields and their `@cache` directive arguments in Magento's `etc/schema.graphqls` files. Fields span several lines, arguments are nested, and modules extend types with `extend type Query`. A line-based scan would be fragile.

Magento itself parses these files with `webonyx/graphql-php`. Its constraint in `magento/framework`, read from the magento/magento2 tags on 2026-10-22:

| Magento | `webonyx/graphql-php` |
|---|---|
| 2.4.4 | `~14.11.3` |
| 2.4.5 | `~14.11.6` |
| 2.4.6, 2.4.7, 2.4.8 | `^15.0` |
| 2.4.9 | `^15.0` (15.34.0 in the test project's lock file) |

## Decision

- The package requires `webonyx/graphql-php: ^14.11 || ^15.0`, which is compatible with every Magento version above.
- Schemas are parsed with `GraphQL\Language\Parser::parse()`. It accepts SDL fragments with custom directives and type extensions without schema validation.
- Rules read the AST nodes (`ObjectTypeDefinitionNode`, `ObjectTypeExtensionNode`, field directives and arguments). These exist with the same API in 14.11 and 15.x.
- Before parsing, `SchemaPreprocessor` brings Magento's dialect to standard SDL, as `Magento\Framework\GraphQlSchemaStitching\GraphQlReader` does: empty types and enums get a placeholder member, and `implements A, B` becomes `implements A & B`. Line numbers are preserved. Without this, 14 core schemas of Magento 2.4.9 did not parse.
- A file that does not parse is skipped and reported as a `graphql` analysis notice.

## Consequences

- The X-GQL tests pass with 15.38.1 (the development install) and with 14.11.10, checked on 2026-10-22 in a temporary copy.
- Composer blocks every 14.11.x release because of security advisories (PKSA-xwpn-zs9j-6wy5, PKSA-sf9j-1gs7-xzvx, PKSA-7h5p-prw9-w5nr) unless `policy.advisories.block` is turned off. Magento 2.4.4 and 2.4.5 projects already depend on 14.11, so installing this package there runs into the same block as any other dependency change in those projects. `--project` mode from a separate checkout avoids it.
- A project that pins webonyx below 14.11 cannot install the package. No supported Magento version does.
