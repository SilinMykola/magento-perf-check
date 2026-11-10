# Design

## What the core does

For each object in a web API request, `ServiceInputProcessor::_createFromArray()` (magento/magento2@a385d02f):

1. takes the requested type: a service method parameter type, or the getter return type of the enclosing object;
2. builds the `di.xml` preference of that type, following preferences to the end of the chain (`ObjectManager\Config\Config::getPreference()`), or the type itself;
3. passes a request value to a constructor parameter only when `TypeProcessor::getParamType()` gives a simple type or one matching `~\\?\w+\\\w+\\Api\\Data\\~` ([#L290-L296](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L290-L296));
4. sets every other field through the setter of the **requested type**, found by exact name (`set<Field>` or `setIs<Field>`); a non-empty value for a field with a getter and no setter fails with `"%fieldName" is not supported` ([#L346-L398](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L346-L398)).

`getParamType()` uses Laminas `ParameterReflection::detectType()`: a builtin native type as is, a native class type by its name, and otherwise the first type of the `@param` tag for that parameter. A native `array` takes the first type of the `@param` tag at the parameter's position, resolved against the class's namespace and `use` statements, with `[]` appended. Simple means `string`, `int`, `float`, `double`, `bool` (and the aliases `str`, `integer`, `boolean`), also as arrays.

## Decisions

- **A collector and a rule on collected data.** A collector records, for each analyzed class, the constructor parameters whose type the core would not fill and for which the class has a getter. The rule runs once per analysis on the collected data: it walks the input types from `webapi.xml` with PHPStan's reflection, maps preferences to the classes built, and checks getters and setters on the requested type. A rule per class would walk the types in every worker, and PHPStan's result cache would keep a stale result for a class when only a service interface elsewhere changed; rules on collected data run on every analysis.
- **Parameter types read the way the core reads them.** The collector parses the constructor's doc comment with the Laminas `@param` pattern and takes the first type, instead of PHPStan's resolved type, whose union order is lost. Like Laminas, it reads tags only at the start of a line and matches a tag without a variable name to no parameter. A relative class name is resolved as a PHPDoc type in the file of the constructor's declaring class, with its imports; a name of no known class stays as written, as in the core.
- **Not reported:** a parameter with a union or intersection native type, an `array` parameter without a `@param` tag at its position, and an untyped one whose first doc type is `null`: the core fails on those before the type check, with an error of its own. A preference to a name that is not a class, such as a virtual type, is skipped.
- **All preferences count.** When modules configure different preferences for one type, the index does not know the module load order, so each configured class is checked. A class replaced by another module's preference can therefore be reported.
- **Input types** start from every service method parameter in `webapi.xml` and follow getter return types (`get*` and `is*` without required parameters) transitively, through all classes PHPStan resolves in those types. The core takes only the first `@return` type, so this can include a few more types than the core builds.
- **Report location:** the parameter in the class's own constructor, or the class declaration when the constructor is inherited.

## Index

`webapi.xml` routes give service class and method. `preference` elements come from `di.xml` in the global, `webapi_rest` and `webapi_soap` areas and from `app/etc/di.xml`. `INDEX_VERSION` goes up.
