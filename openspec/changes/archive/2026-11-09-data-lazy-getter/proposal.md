# Proposal

## Why

P-DI-06 leaves out the lazy getter that fills a property once, but not the same getter written with the object's data: `$viewModel = $this->getData('style_view_model'); if (!$viewModel) { $viewModel = ObjectManager::getInstance()->get(Style::class); $this->setData('style_view_model', $viewModel); }`. The object manager is asked once per block here too, and layout can pass the view model in as an argument. Seen twice in a third-party blog module on a Magento 2.3.3 project and graded as false positives.

## What Changes

- **P-DI-06:** a lazy getter is also left out when it tests `$this->getData('key')`, directly or through a variable read from it alone, and only stores one object manager call, or an array of them, with `$this->setData('key', ...)` under the same literal key.
- Still reported: another key, a key that is not a literal, `getData()` with an index, another object's data, other work in the block, and a variable read from two keys.

## Impact

Fewer P-DI-06 findings, at `info`; none added.
