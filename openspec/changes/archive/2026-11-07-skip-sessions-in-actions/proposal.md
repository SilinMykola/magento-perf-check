# Proposal

## Why

P-DI-01 reports a session injected without a Proxy, because the session starts when the object is built. In a controller action that costs nothing: on the frontend the core customer `ContextPlugin` reads the session before every action, and an admin request is validated against the admin session before its action runs. A controller is built only to run. In the checks on modules the rules were not tuned on, every graded P-DI-01 finding in a controller was low impact.

## What Changes

- **P-DI-01:** a session parameter of a class that implements `ActionInterface` is not reported. A cookie manager there still is, because the Technical guidelines forbid it in constructors.

## Impact

The default set changes: on 78 modules, 42 of 182 P-DI-01 findings go, all in controllers. The 6 of them graded in calibration were all TP-low.
