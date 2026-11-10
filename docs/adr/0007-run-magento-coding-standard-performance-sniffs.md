# 7. Run magento-coding-standard's performance sniffs instead of copying them

- Status: Accepted
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
| `Magento2.Functions.DiscouragedFunction` | among many other functions, `sleep()`, `time_nanosleep()` and `session_*()` | warning |

On the calibration corpus (`app/code`, test code included, 2026-11-10), MCS v41 with these sniffs reported 34 ForeachArrayMerge, 28 ThisInTemplate, 8 DiscouragedDependencies, no Templates.ObjectManager, and 202 DiscouragedFunction, of which 2 were `sleep()` or `session_*()`. MCS is usually installed already. Seen in November 2026: the Composer project of Magento 2.4.9 used for calibration lists it in `require-dev`, and so do three of four other Magento projects checked; the fourth gets it through a GrumPHP package. The installed versions range from 5 to 40.

## Decision

- The tool does not copy these sniffs. When the analyzed project has MCS installed, the `magento-perf-check` command runs the project's `phpcs` with only these sniffs, on the same paths, as a subprocess, and merges the results into its report.
- Each sniff becomes a rule with an ID of the tool's own pattern (S-PERF-01 and so on), so configuration, the baseline, `--fail-on` and the DDEV wrapper, which expect that pattern, need no change. The message names the sniff, and the rule page links to it. `DiscouragedFunction` findings are kept only for `sleep()`, `time_nanosleep()` and `session_*()`, the ones in its list that are about performance.
- Configuration, severity overrides, `--fail-on` and the baseline apply to them like to any other rule. They are experimental: their only sources are the sniffs (level C) and they are not calibrated. phpcs runs only when one of them is enabled, so a project that already runs MCS in CI simply leaves them off.
- MCS stays out of `require`, as bitExpert does ([ADR 2](0002-bitexpert-phpstan-magento-as-suggestion.md)). Without it, the group does not run and one notice says so.
- The implementation starts as an OpenSpec change. It must keep the JSON report fields and exit codes that the DDEV wrapper reads.

## Consequences

- One report and one baseline cover the performance checks of both tools, without a second implementation to keep in step with MCS.
- A project that runs MCS in CI and enables these rules sees the same lines in both reports; leaving them off avoids that.
- The tool depends on the project's MCS version and on its sniff codes. phpcs skips an unknown sniff without a word when another one is found, so the command checks that each sniff's file exists in the installed version and gives a notice for a missing one; `Magento2.Templates.ObjectManager`, for example, exists only since version 36.
- SPEC section 4.9 stays: the checks are still not implemented here, they are run.
