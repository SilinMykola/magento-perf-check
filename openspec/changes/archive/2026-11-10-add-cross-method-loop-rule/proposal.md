# Proposal

## Why

P-LOOP-01, P-LOOP-09 and P-LOOP-10 report a query only when it is written in the loop body itself. The common shape is one call away: the loop calls a method of the same class or of an injected service, and that method loads a model or calls a repository. Adobe's code review checklist names it: "Are queries executed in a loop? This loop can be outside of the edited files." SPEC milestone M4 plans this as cross-method loop analysis through PHPStan collectors.

## What Changes

- **New experimental rule P-LOOP-12**, at severity `warning`. It reports a method call inside a loop when the called method, declared in the analyzed code, runs a query on every call. A query is a call that P-LOOP-01, P-LOOP-09 or P-LOOP-10 would report inside a loop:
  - `load()` on a model;
  - `get()` or `getById()` on a repository;
  - `getList()` with search criteria on a repository;
  - `load()` on a resource model.

  The message names the called method and the query, with its file and line.
- **Collectors.** One records the query calls per method. One records the methods that keep results in a property array, which are left out because they query once per key. One records the method calls made in loops. A rule on the collected data joins them, as P-API-01 does.
- **One level only.** The called method must contain the query itself; deeper chains are left for a later change, after this one is measured.
- **Docs and data.** Catalog entry, rule page with a Basis section, fixtures for every condition, a measurement on the corpus and on the core, and the changelog.
