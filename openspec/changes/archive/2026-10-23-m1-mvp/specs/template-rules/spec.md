# Spec Delta

## Purpose

Rules on `.phtml` templates that catch logic which belongs in blocks, view models or layout rather than in the template.

## ADDED Requirements

### Requirement: T-01 createBlock in a template
T-01 SHALL report, as `warning`, a call chain `->getLayout()->createBlock(...)` in a `.phtml` file, on any receiver. The message SHALL say that the Technical guidelines forbid it and that the block should be declared in layout XML instead.

#### Scenario: Block created in template
- **WHEN** a template contains `$block->getLayout()->createBlock(\Acme\Promo\Block\Badge::class)->toHtml()`
- **THEN** T-01 reports a warning

#### Scenario: Child block from layout
- **WHEN** a template calls `$block->getChildHtml('badge')`
- **THEN** T-01 reports nothing

#### Scenario: createBlock in a PHP class
- **WHEN** a block class calls `$this->getLayout()->createBlock(...)`
- **THEN** T-01 reports nothing
