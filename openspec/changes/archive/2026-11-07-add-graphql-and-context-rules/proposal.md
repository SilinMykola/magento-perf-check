# Proposal

## Why

Testing on GraphQL modules, which the 40 modules checked so far barely include, showed two things. Three of 14 schemas were skipped with a parse notice, although Magento reads them: `// @codingStandardsIgnoreFile`, a license block in `/** */`, `extends type`. So X-GQL-01 saw nothing in those files. And custom fields on `ProductInterface` that run a query per product, the most common N+1 in GraphQL, have no rule yet.

Two rules from the catalog stay planned although their sources are checked: X-GQL-02 and P-FPC-03.

## What Changes

- **Schema dialect:** block and line comments, and `extends type`, parse the way Magento's `GraphQlReader` tolerates them. Line numbers stay.
- **X-GQL-02:** a database query made directly in a `ResolverInterface` resolver of a field whose type is reachable through a list. A resolver that only formats values, as most do, is not reported, unlike the catalog's original wording: the N+1 comes from the query. Queries deferred into a closure are not reported.
- **P-FPC-03:** a customer id, email, session id, quote id or the current time as a value of `Http\Context::setValue()`.
- **Project index:** reads `schema.graphqls` and also finds modules in a package's `src/` directory. Its cache key gets a version, so a change in how the index is built takes effect without touching the project.
- **Resource models:** P-LOOP-03, P-LOOP-10, P-FPC-06 and X-SETUP-01 also recognize EAV resource models, which they missed. On the 40 modules checked before this change, no finding was added or removed.

## Impact

Both rules start experimental, so no default result changes. The dialect change can add X-GQL-01 findings in schemas that used to be skipped, and it removes their `graphql` notices.
