# Help calibrate the rules

A rule stays in the default set only if it is right on real code. The public calibration corpus (the Magento core, the 25 most-downloaded Packagist modules and an extended part of 100 more, [`corpus.json`](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/corpus.json)) has little of the custom code that stores run, so many rules have too few findings to measure. Counted on 2026-11-13 from the latest round of each rule in [`docs/calibration/`](https://github.com/SilinMykola/magento-perf-check/tree/main/docs/calibration), 25 of the 37 rules in the default set have fewer than the 10 graded findings a decision needs. Running the tool on your project and telling us which findings are right is the most useful help there is.

You do not need to send code, except for false positives (see [Code for a false positive](#code-for-a-false-positive)). Share only what you may share: if the project belongs to a client or an employer, ask first.

## 1. Run the tool with every rule on

You need magento-perf-check 0.7.1 or newer, and PHP 8.1 or newer on the machine that runs it, whatever PHP the store itself runs on. The project needs its own `composer install`; no database or container is needed. The tool loads the project's autoloader, which runs every module's `registration.php`: run it only on code you trust, or in a sandbox.

The clone does not touch the project, and it is the only way on Magento 2.4.4 and older, where `composer require` conflicts ([Magento versions](configuration.md#magento-versions)):

```sh
git clone https://github.com/SilinMykola/magento-perf-check.git && cd magento-perf-check && composer install
php bin/magento-perf-check --project=/path/to/magento --config=resources/all-rules.neon --format=json --output=perf-check-report.json
```

Or install it into the project, and remove it afterwards with `composer remove --dev silin/magento-perf-check`. Write the report outside the project, so that it is not committed by accident:

```sh
composer require --dev silin/magento-perf-check
vendor/bin/magento-perf-check --config=vendor/silin/magento-perf-check/resources/all-rules.neon --format=json --output=../perf-check-report.json
```

[`resources/all-rules.neon`](../resources/all-rules.neon) switches on the experimental rules too. The run analyzes `app/code` and `app/design`. PHPStan keeps a cache, so the first run is the slow one: on the Magento 2.4.9 core it took 84.5 s with a cold cache and 2.7 s on the next run ([round 15, Timing](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-15.md#timing)).

Exit code 0 or 1 means the run finished (1: at least one finding at `error`). Exit code 2 means it failed or is incomplete, and the reason is on the console. If PHPStan ran out of memory, run again with `--memory-limit=4G`; a report with `"incomplete": true` is missing its PHP findings. Notices with the source `coding-standard` (the project has no magento/magento-coding-standard, or one without a sniff an S-* rule runs) or `upgrade-version` (the project is not on the version an upgrade rule checks) are normal.

## 2. Grade the findings

Open each finding, read the code around it and give it one verdict. Each rule's page, linked from the finding, says what the rule claims.

| Verdict | Meaning |
|---|---|
| `TP` | The finding is right and it matters: a query per item of a real list, a page that loses Full Page Cache, a file loaded on every page. |
| `TP-low` | The finding is right, but costs little: a loop over three items, an admin-only page, a script on one rarely visited page, a file Magento does not load now. |
| `FP` | The finding is wrong: the code does not do what the rule says. |
| `unclear` | You cannot tell. |

Add one line on why, without names or paths, for example "the loop runs over the cart items, at most a few" or "the block is only on the customer's order page". If a rule gives more than 30 findings, grading 30 of them is enough; say how you picked them.

A finding in a module you do not maintain counts as well, and so does one in a theme file of a module that is not installed: the file is in the project.

### Code for a false positive

A `FP` verdict says that the rule is wrong, and `unclear` that you could not tell; neither says why, and fixing the rule depends on the shape of the code it reported. For those findings, if your agreement with the code's owner allows it, send the smallest piece of code that shows the case: the method, layout block or script around the reported line. Rename classes, variables and strings as you like, and drop the business logic, but keep what the rule looks at: the loop, the call and its receiver, the types, the handle, and the form of a module ID (`Vendor_Module`), not its name. If you cannot send code, describe that shape in words.

Send code only privately (step 3), never in a public issue. It is not committed or published: we write our own small fixture with the same shape, without copying your code, and only that fixture goes into the tests. Your piece is deleted once that fixture is written, or once we decide no change is needed. A `TP` or `TP-low` finding needs no code.

## 3. Send the report

Include:
- the Magento version and edition (Open Source or Adobe Commerce), and whether the storefront uses Luma, Hyvä or something else;
- the tool version (`php bin/magento-perf-check --version`, or `vendor/bin/magento-perf-check --version`);
- from `summary` in the report: `byRule` (findings per rule), `notices` (notices per source) and `durationSeconds`. Do not paste the whole summary or the `notices` list: they hold the project's path and module names;
- your verdicts: rule ID, verdict and the line on why, one row per finding;
- for `FP` and `unclear` findings, the code pieces of [Code for a false positive](#code-for-a-false-positive), privately;
- for exit code 2, the reason the console showed.

Leave out anything you cannot share. A count per rule with verdicts helps even without the reasons; a count alone helps little.

Where to send it:
- **Privately:** to the person who sent you this page. If you found it yourself, open an issue that says only that you have a report, and the maintainer will answer with a private channel; keep any code until then, out of the issue.
- **Publicly:** an issue with the [calibration report template](https://github.com/SilinMykola/magento-perf-check/issues/new?template=calibration-report.yml). An issue is public as soon as it is posted and shows your GitHub account, so use it only for what may be public.

## 4. What happens next

What is published from a report is counts and neutral descriptions only, as in [round 15](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-15.md): no project, client or vendor name, no path, no file content. A public issue itself stays as you wrote it.

Each report is graded into a calibration round with the [method](calibration.md#method) of round 1: the same verdicts and thresholds. A rule can join the default set with at least 10 graded findings, a precision at its severity's threshold (75 % for `warning`) and an impact share of at least 50 % ([round 6](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-6.md)); one that is wrong too often leaves it; and a false positive that repeats becomes a fix. The rounds are in [`docs/calibration/`](https://github.com/SilinMykola/magento-perf-check/tree/main/docs/calibration); [rounds 14 to 16](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-14.md) used private stores.
