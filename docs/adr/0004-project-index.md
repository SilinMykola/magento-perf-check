# 4. Project-wide index of Magento configuration

- Status: Accepted
- Date: 2026-10-22

## Context

Several M1 rules need facts that live outside the file or module they analyze:
- **P-DI-01:** a Proxy for a constructor argument is often configured in another module's `di.xml`, or only in one area.
- **X-DB-01:** a module may add columns to a table whose primary key another module declares. In Magento 2.4.9, `Magento_GiftMessage` extends `sales_order` this way.
- **X-IDX-01:** an indexer's mview view can be declared by another module.
- **P-FPC-10, P-PLG-01:** plugin classes have no interface; only `di.xml` says a class is a plugin.

The analyzed paths are usually a subset of the project (`app/code`, or one module). Facts must still come from every module, core included, while findings stay limited to the analyzed paths.

## Decision

- `ProjectIndexBuilder` builds the index from module roots:
  - `app/code/<Vendor>/<Module>`;
  - every package in `vendor/composer/installed.json` whose install path contains `etc/module.xml`.

  This avoids walking the whole `vendor` tree.
- It reads only `etc/di.xml`, `etc/<area>/di.xml`, `etc/events.xml`, `etc/<area>/events.xml`, `etc/mview.xml`, `etc/db_schema.xml` and `app/etc/di.xml`.
- **Indexed facts:**
  - proxied constructor arguments per class; a Proxy on a virtual type also counts for the class it is based on;
  - plugin classes;
  - observers per event;
  - mview view IDs;
  - tables with a primary key.
- **Cache:** the index goes to `<system temp>/magento-perf-check-<uid>/<md5(project root)>/index-<fingerprint>.json`. The fingerprint covers the path, size and mtime of every indexed file, so any change produces a new index. Older index files in that directory are deleted. Nothing is written into the project.
- **Who builds it:**
  - `magento-perf-check` builds the index before starting PHPStan and passes `magentoPerf.projectRoot`. PHPStan workers then read the cached file.
  - In a project's own PHPStan setup, `ProjectIndexProvider` builds it lazily from `%currentWorkingDirectory%`.
- **Empty index:** when no module is found, the index is empty, and index-dependent rules report nothing instead of guessing.

Measured on 2026-10-22 on `/path/to/magento-2.4.9` (Apple M4, PHP 8.5.11), 390 modules:
- cold build: 1.67 s;
- cached load: 0.04 s;
- peak memory: 7 MB.

The index held 159 classes with proxied arguments, 809 plugin classes, 269 observed events, 17 mview views and 368 tables with a primary key. The project was unchanged afterwards.

## Consequences

- Rules can rely on project-wide facts without scanning on their own, at a cost of under 2 seconds per cold run on a full Magento install.
- Modules installed outside Composer and outside `app/code`, for example a custom path in `app/etc/NonComposerComponentRegistration.php`, are not indexed. If that becomes a problem, registration files can be parsed later.
- Proxies configured on a parent class are not copied to subclasses in the index. Rules that need this, such as P-DI-01, walk the class hierarchy through PHPStan reflection and query each ancestor.
