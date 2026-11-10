# P-API-01: web API input field that only an unsupported constructor parameter could set

| | |
|---|---|
| Severity | `warning` (`magentoPerf.webapiConstructorParameter`) |
| Status | experimental, off by default: [calibration round 5](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-5.md) found 1 finding (fewer than 10: insufficient data). Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | PHPStan, with the project index of `webapi.xml` and `di.xml` |
| Protects | compatibility ([ADR 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0008-scope-beyond-performance.md)); it has no performance cost |

## What it catches

A constructor parameter of a class that a REST or SOAP request builds, when the request cannot fill that parameter and nothing else sets the field. All of these must hold:

- **Input type.** The type is a parameter type of a service method that a `webapi.xml` route calls, or the return type of a getter (`get*`, `is*`) of such a type, at any depth.
- **Class built.** The class is the end of the type's `<preference>` chain in `di.xml`, in the global, `webapi_rest` or `webapi_soap` area, or the type itself when it has no preference.
- **Unsupported parameter.** The core gives the parameter no value from the request: its type is neither simple (`string`, `int`, `float`, `double`, `bool`, also as an array) nor matches `\Vendor\Module\Api\Data\`.
- **No setter.** The input type has a public getter for the field (`get<Field>` or `is<Field>`), but no `set<Field>` or `setIs<Field>`. `<Field>` is the parameter name in upper camel case, and the names must match exactly.

The parameter's type is read the way the core reads it, which is not always the PHP type:
- a native class type is taken by its name;
- a native `array` takes the first type of the `@param` tag at its position, whatever variable the tag names;
- a parameter without a native type takes the first type of the `@param` tag that names it.

Only tags at the start of a doc comment line count, and a relative name is resolved against the imports of the class that declares the constructor.

So `?array $sort` with `@param SortOrder[]|null $sort` is a `SortOrder[]`.

The rule does not report:
- a parameter whose native type is a union or an intersection;
- an `array` parameter without a `@param` tag at its position;
- a parameter without a native type whose first doc type is `null`.

The core fails on these before it checks the type, with an error of its own.

When the project has no Magento configuration, there are no routes, and the rule reports nothing.

## Why it breaks requests

Adobe Security Bulletin APSB25-88 changed how web API requests build data objects; the patch is in 2.4.9 and the supported earlier versions. `ServiceInputProcessor` asks the object manager for the type's preference. It passes a request value to a constructor parameter only for the types above, and sets every other field through the setter of the **requested type**. A request that sends a non-empty value for a field with a getter and no setter on that type fails as a whole:

```json
{"message":"\"%fieldName\" is not supported. Correct the field name and try again.","parameters":{"fieldName":"Sort"}}
```

Before 2.4.7 the message is `Property "Sort" does not have accessor method "setSort" in class ...`. Nothing fails at build time, and Adobe asks developers of existing REST APIs to review their constructors for this.

Seen on 2026-11-10 on Magento 2.4.9 with Inventory 1.2.9: `POST /rest/V1/inventory/in-store-pickup/pickup-locations/` with a sort order fails this way, and the core run of the rule reports exactly that field.

## Example

Bad:

```php
interface SearchRequestInterface
{
    /** @return \Magento\Framework\Api\SortOrder[]|null */
    public function getSort(): ?array;
}

class SearchRequest implements SearchRequestInterface
{
    /** @param SortOrder[]|null $sort */
    public function __construct(private ?array $sort = null)
    {
    }
}
```

Good: declare the setter on the type that the service method or the parent getter names. A setter only on the class is not used:

```php
interface SearchRequestInterface
{
    /** @return \Magento\Framework\Api\SortOrder[]|null */
    public function getSort(): ?array;

    /** @param \Magento\Framework\Api\SortOrder[]|null $sort */
    public function setSort(?array $sort): void;
}
```

There is a second fix when the field's type can live in an `Api\Data` namespace: give the parameter that type.

## How to suppress

```php
// @phpstan-ignore magentoPerf.webapiConstructorParameter (the field is never sent by clients)
```

Put it on the line of the parameter, or of the class when the constructor is inherited.

## Known false positives

- When several modules configure different preferences for one type, every configured class is checked, because the index does not know the module load order. A class replaced by another module's preference can be reported.
- A field that clients never send still has a getter, so it is in the schema. A request with it fails, so the finding is correct, but it may never happen in practice.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Web API services](https://developer.adobe.com/commerce/php/development/components/web-api/services) | A | "REST API constructor parameter validation": supported types are simple types and `*\Api\Data\*Interface` classes; "Unsupported parameters will not be instantiated from REST payloads." It also gives both error messages. |
| [ServiceInputProcessor.php#L229-L241](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L229-L241) | B | The constructor is taken from the preference of the requested type. |
| [ServiceInputProcessor.php#L290-L296](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L290-L296) | B | A parameter gets a request value only for a simple type or one matching `~\\?\w+\\\w+\\Api\\Data\\~`. |
| [ServiceInputProcessor.php#L346-L398](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L346-L398) | B | Other fields go through the getter and setter of the requested type. A missing setter ends in `"%fieldName" is not supported`. |
| [NameFinder.php#L73-L78](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Reflection/NameFinder.php#L73-L78) | B | The setter is `set<Field>` or `setIs<Field>`. `hasMethod()` compares the name exactly. |
| [TypeProcessor.php#L538-L564](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Reflection/TypeProcessor.php#L538-L564), [#L942-L948](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Reflection/TypeProcessor.php#L942-L948) | B | `getParamType()`: the native type or the first `@param` type; an array takes the tag at the parameter's position. |

Verified 2026-11-10.
