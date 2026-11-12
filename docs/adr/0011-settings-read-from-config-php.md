# 11. Settings are read from app/etc/config.php by a token walker

- Status: Accepted
- Date: 2026-11-12

## Context

C-02 is the first rule on a store setting rather than on code: `dev/js/merge_files` switched on in the `system` section of `app/etc/config.php`. The file is PHP that returns a nested array. Until now the tool read only its `modules` list, with a regular expression ([project-index spec](../../openspec/specs/project-index/spec.md)); a setting sits five or six keys deep, which a regular expression cannot follow.

Running the file would give the array at once, but the tool never runs project code (SPEC section 9a), and a `config.php` may call functions or read constants.

`app/etc/env.php` holds settings too. It is usually not in the repository and holds credentials, and SPEC section 4.8 already keeps it out; this ADR does not change that.

## Decision

- `src/Project/ConfigPhpValues.php` walks the tokens of the file: it finds the first `return` outside any braces, follows the array literal after it (`[...]` or `array(...)`), and gives each value that is a literal (a string, a number, `true`, `false` or `null`) with its key path, outermost first, and its line. A value or key that is not a literal is left out, with everything under it. A list element gets the index PHP would give it, unless a key before it in the same array is not a literal: its value, and so the next index, is not known.
- Settings rules use the prefix `C-`, as C-01 in SPEC section 4.8. They run outside both analyzers, on the project root.
- The file is read only when a settings rule is enabled, and whatever `--paths` says: its settings apply to the whole store, not to the code under a path.
- A finding cannot be suppressed by a comment: Magento writes the file with `app:config:dump` and `config:set --lock-config`, from the array it loaded and writes back only its own section comments ([Writer.php#L127-L146](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/DeploymentConfig/Writer.php#L127-L146)), so no comment next to a value survives. The rule is disabled in the configuration file, or a finding kept in a baseline.

## Consequences

- A key given twice keeps only its last value, and an array literal that is only part of an expression gives no values, as PHP would have it.
- A rule compares key paths, as the script rules compare property paths ([ADR 10](0010-property-paths-in-the-script-scanner.md)).
- A setting that `config.php` builds in code, such as `'merge_files' => getenv('MERGE')`, is not seen. Magento itself writes the file with `var_export()`, which gives only literals.
- The `modules` list is still read by its own regular expression. Moving it to the walker is possible, but it would change a behaviour that works; it is left as it is.
- Settings in `env.php`, in environment variables and in the database are not seen. Magento merges `env.php` over `config.php`, so a value it overrides is still reported; the rule pages say so. A run without the `system` section in `config.php`, as on the calibration corpus, finds nothing, so settings rules cannot be calibrated on the public corpus.
