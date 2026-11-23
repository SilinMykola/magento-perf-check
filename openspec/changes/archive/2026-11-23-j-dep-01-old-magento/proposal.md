# Proposal

## Why

Before Magento 2.3.3 core maps `jquery/ui` to `jquery/jquery-ui`, the single jQuery UI file, and has no `jquery-ui-modules/*` to depend on instead. J-DEP-01's message (core maps it to `jquery/compat`) and its advice are wrong there; the rule page already lists such projects as a known false positive. On three private Magento 2.1.7 and 2.2.3 projects all 17 graded J-DEP-01 findings were this case.

## What Changes

- A rule definition can name the first Magento version where its advice applies (`appliesFrom`). The command drops its findings when the version installed in `vendor/magento/magento2-base` is older; the target version does not count, as such a rule describes the store as it is. With no readable version the rule runs.
- J-DEP-01 applies from 2.3.3.
