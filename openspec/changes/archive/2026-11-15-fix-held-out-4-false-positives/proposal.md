# Proposal

## Why

The fourth held-out check graded up to 12 findings per rule of the default set on the 100 modules of the `github2` corpus part. Of 104 graded findings, 9 were false; 7 of them come from three gaps that the rules can close:
- **P-FPC-05** reported all five of its findings wrongly. Four set `Cache-Control` to `sprintf('public, max-age=%d', $ttl)`, a computed value that asks for caching; the rule takes any computed value as one that may disable it. The fifth calls `setHeader()` on what `$page->getLayout()->getBlock()` returns, a block, where it only sets the block's `header` data (`AbstractBlock` extends `DataObject`).
- **P-LOOP-01** reported `$this->productAttributeRepository->get($id)` in a loop over stores where `$id` never changes: the attribute repository goes through `Eav\Model\Config::getAttribute()`, which keeps the attribute, so only the first iteration queries. The product, category, customer, order, cart and attribute repositories of core all keep what they load by their arguments.
- **P-DI-06** reported a lazy getter that first keeps the object manager in a local variable: `$objectManager = ObjectManager::getInstance(); $this->productRepository = $objectManager->get(...)`, the allowed form written in two statements.

The other two false findings are X-EVT-02 on a New Relic observer that records a metric for every saved and deleted model, the generic monitoring the rule page lists as a known false positive.

## What Changes

- P-FPC-05 judges a computed `Cache-Control` value by its literal parts and leaves out `setHeader()` on a block.
- P-LOOP-01 leaves out `get()` and `getById()` on those caching repositories when no enclosing loop changes any argument.
- P-DI-06 accepts the lazy getter that keeps the object manager in a local variable first.
