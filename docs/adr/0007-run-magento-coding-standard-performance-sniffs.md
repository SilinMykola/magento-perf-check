# 7. Run magento-coding-standard's performance sniffs instead of copying them

- Status: Accepted, not implemented yet
- Date: 2026-11-10

## Context

[magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) (MCS) is Adobe's PHP_CodeSniffer standard. A few of its sniffs are about performance, and [SPEC section 4.9](../SPEC.md) leaves them out of this tool so that nothing is reported twice. Users who do not run MCS miss those checks, and users who do see two reports with two baselines.

The sniffs in question, at commit [e4af7c6](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/ruleset.xml) (tag v41):

| Sniff | What it reports | Type in the ruleset |
|---|---|---|
| `Magento2.Performance.ForeachArrayMerge` | `array_merge()` inside a loop | warning |
| `Magento2.Templates.ThisInTemplate` | `$this` in a template, including `$this->helper()` | warning |
| `Magento2.Templates.ObjectManager` | the object manager in a template | warning |
| `Magento2.Classes.DiscouragedDependencies` | a Proxy or Interceptor named in a constructor | error |
| `Magento2.Functions.DiscouragedFunction` | among many other functions, `sleep()`, `usleep()` and `session_*()` | warning |

On the calibration corpus (`app/code`, 2026-11-10), MCS v41 with these sniffs reported 34 ForeachArrayMerge, 28 ThisInTemplate, 8 DiscouragedDependencies, no Templates.ObjectManager, and 202 DiscouragedFunction, of which 2 were `sleep()` or `session_*()`. MCS is usually installed already. Seen in November 2026: the Composer project of Magento 2.4.9 used for calibration lists it in `require-dev`, and so do three of four other Magento projects checked; the fourth gets it through a GrumPHP package. The installed versions range from 5 to 40.

## Decision

- The tool does not copy these sniffs. When the analyzed project has MCS installed, the `magento-perf-check` command runs the project's `phpcs` with only these sniffs, on the same paths, as a subprocess, and merges the results into its report.
- Each such finding keeps the sniff code as its rule ID, for example `Magento2.Performance.ForeachArrayMerge`, and links to the sniff's documentation in MCS. `DiscouragedFunction` findings are kept only for `sleep()`, `usleep()` and `session_*()`.
- Configuration, severity overrides, `--fail-on` and the baseline apply to them like to any other rule. A configuration switch turns the whole group off for projects that already run MCS in CI.
- MCS stays out of `require`, as bitExpert does ([ADR 2](0002-bitexpert-phpstan-magento-as-suggestion.md)). Without it, the group does not run and one notice says so.
- The implementation starts as an OpenSpec change. It must keep the JSON report fields and exit codes that the DDEV wrapper reads.

## Consequences

- One report and one baseline cover the performance checks of both tools, without a second implementation to keep in step with MCS.
- A project that runs MCS in CI and keeps the group on sees these lines in both reports; the switch exists for that case.
- The tool depends on the project's MCS version and on its sniff codes. A renamed sniff gives a notice, not a silent gap, and the version range is tested in CI.
- SPEC section 4.9 stays: the checks are still not implemented here, they are run.
