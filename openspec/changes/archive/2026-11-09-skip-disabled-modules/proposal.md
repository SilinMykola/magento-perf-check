# Proposal

## Why

A module set to `0` in `app/etc/config.php` does not run: Magento loads neither its code nor its configuration. The tool still reported its findings and counted its `di.xml`, `events.xml` and other files in the project index. On a Magento 2.3.3 project, 43 of 177 findings were in four disabled modules, and a reader had to find out by hand that none of them mattered.

## What Changes

- **Project index:** a module set to `0` in the `modules` list of `app/etc/config.php` is left out of the index, so its plugins, observers, Proxy arguments and other configuration do not count. A module missing from the list, or a project without the file, counts as enabled: a new module that has not been enabled yet is still checked. The module list is part of the cache key, so enabling or disabling a module, or adding one with no indexed file, invalidates the cached index and PHPStan's cached results.
- **Report:** findings in files of such a module are not reported. Each one becomes a `disabled-module` notice that names the rule, the location and the module, so the summary line says how many were left out.
- `app/etc/config.php` is read as text, not run.

## Impact

Fewer findings, never new ones, on projects with disabled modules; with a disabled module's configuration out of the index, a rule that depends on it can also report more, for example P-DI-01 when the only Proxy for an argument was configured in a disabled module.
