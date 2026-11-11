# Proposal

## Why

[ADR 8](../../../docs/adr/0008-scope-beyond-performance.md) takes compatibility rules into scope and names the first one. Since Adobe Security Bulletin APSB25-88, a REST or SOAP request no longer fills every constructor parameter of a data object: [Web API services](https://developer.adobe.com/commerce/php/development/components/web-api/services) lists only simple types and `*\Api\Data\*Interface` as supported, and `ServiceInputProcessor::getConstructorData()` skips any other parameter ([ServiceInputProcessor.php#L290-L296](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L290-L296)). A field that only such a parameter could set, with no setter on its type, is then rejected with `"%fieldName" is not supported`. Nothing fails at build time; the request fails after the upgrade.

A first scan of a Magento 2.4.9 project found one such field in the core: `sort` of the in-store pickup search request, confirmed on a running 2.4.9 store.

## What Changes

- **P-API-01**, experimental, `warning`: a constructor parameter of a web API input data object that the request cannot fill, for a field its type declares a getter for and no setter. The rule follows what the core does:
  - input types are the parameter types of the service methods in `webapi.xml`, and the getter return types of those types, transitively;
  - the object built for a type is its `di.xml` preference in the global, `webapi_rest` or `webapi_soap` area, followed to the end of the chain, or the type itself when it has none;
  - a parameter's type is taken the way `TypeProcessor::getParamType()` takes it: the native type, or for `array` and untyped parameters the first type of the `@param` tag; it is supported when simple or when it matches the core's `Api\Data` pattern;
  - the getter and setter are looked up on the requested type, by exact name, as `NameFinder` does.
- **Project index:** reads `webapi.xml` (service class and method of each route) and the `preference` elements of `di.xml` in those three areas.
- **Documentation:** rule page, catalog entry, SPEC row, rules list, README count, changelog.

## Impact

The rule is experimental, so no default result changes. The rule list grows to 54, 20 of them experimental. The index cache version goes up, so the first run after the update rebuilds the index.
