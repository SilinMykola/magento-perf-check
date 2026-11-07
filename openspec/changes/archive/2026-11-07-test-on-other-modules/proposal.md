# Proposal

## Why

Running the tool module by module, and on 15 modules the rules were not tuned on, found what the corpus runs did not: a stub of a core class in one module's `Test` directory changes types for all other code, a disabled rule prints notices, and two rules report code that a check of the same function could rule out.

## What Changes

- **Test code:** left out of PHPStan's symbol discovery, not only of the analysis.
- **Notices:** those about receivers of unknown type appear only when P-COL-02 is enabled.
- **P-COL-02:** silent when the same function uses the rows anyway.
- **P-COL-01, P-COL-02:** `getColumnValues()` counts as using the rows.
- **P-FPC-05:** a call on `$this` is not the response.
- **Docs:** results on the 15 modules.

## Impact

A project with stubs in test code gets findings it did not get before, which can change a CI result.
