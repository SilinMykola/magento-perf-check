# S-DI-01: Proxy or Interceptor requested in a constructor

| | |
|---|---|
| Severity | `info` (a recommendation: in calibration rounds 4 and 6 together, 9 of 10 graded findings were correct and none mattered) |
| Status | experimental, off by default: its sources state a design rule, and in [calibration round 4](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-4.md) none of its 8 findings had a performance cost (fewer than 10: insufficient data). Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | phpcs of the project, sniff `Magento2.Classes.DiscouragedDependencies` of [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) ([ADR 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0007-run-magento-coding-standard-performance-sniffs.md)) |

## What it catches

A constructor parameter typed with a generated `\Proxy` or `\Interceptor` class.

The command runs this sniff only when the rule is enabled and the project has phpcs (`vendor/squizlabs/php_codesniffer`) and `vendor/magento/magento-coding-standard`; otherwise a `coding-standard` notice says why it did not run. The result is the sniff's, in the version the project has installed. The coding standard's ruleset also leaves this sniff out of `_files`, `Fixtures`, `Test` and `tests` directories and `*Test.php` files, even with `--include-tests`. phpcs compares those patterns with the absolute path, ignoring case, so a project that itself lies in such a directory (for example under a `tests` directory) gets no finding of this rule; a `coding-standard` notice says so.

## Why it hurts performance

It costs nothing by itself: a Proxy named in the constructor still builds the real object only on its first use. The rule is here because the choice is then fixed in code: a preference for the type no longer applies, and the Proxy cannot be dropped where the dependency is needed at once. Configure the Proxy for the argument in `di.xml` instead, where [P-DI-01](P-DI-01.md) looks for it.

## Example

Bad:

```php
public function __construct(\Magento\Customer\Model\Session\Proxy $session)
```

Good:

```php
public function __construct(\Magento\Customer\Model\Session $session)
// di.xml: <argument name="session" xsi:type="object">Magento\Customer\Model\Session\Proxy</argument>
```

## How to suppress

```php
// phpcs:ignore Magento2.Classes.DiscouragedDependencies -- reason
```

phpcs reads the comment; a baseline works too.

## Known false positives

- None known.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Proxy.php#L113-L120](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/ObjectManager/Code/Generator/Proxy.php#L113-L120) | B | A generated Proxy builds the real object on its first use, through the object manager. |
| [Adobe: Technical guidelines 2.5](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines#class-design) | A | "Proxies and interceptors MUST NEVER be explicitly requested in constructors." A design rule; it says nothing about speed. |
| [magento-coding-standard: DiscouragedDependenciesSniff.php#L25](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Classes/DiscouragedDependenciesSniff.php#L25) | C | "Proxies and interceptors MUST never be explicitly requested in constructors." |

Verified 2026-11-10.
