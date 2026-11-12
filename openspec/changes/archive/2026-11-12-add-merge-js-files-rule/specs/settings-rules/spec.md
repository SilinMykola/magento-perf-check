## ADDED Requirements

### Requirement: Settings read from app/etc/config.php
The tool SHALL read the settings of `app/etc/config.php` from its tokens and SHALL NOT run the file. It SHALL take the array that the file returns and give every value that is a literal (a string, a number, `true`, `false` or `null`) with the keys that lead to it, outermost first, and its line. A value or key that is not a literal, such as a constant or a function call, SHALL be left out, with everything under it, and so SHALL an array literal that is only part of an expression. A key given twice SHALL keep only its last value. A project without the file, or a file that returns no array literal, SHALL give no values.

#### Scenario: Nested keys
- **WHEN** `app/etc/config.php` returns `['system' => ['default' => ['dev' => ['js' => ['merge_files' => '1']]]]]`
- **THEN** the value `1` has the keys `system`, `default`, `dev`, `js`, `merge_files` and the line of the literal

#### Scenario: Value that is not a literal
- **WHEN** a value is `\Acme\Flag::ON` or `'1' . ''`
- **THEN** the reader gives no value for that key

### Requirement: When app/etc/config.php is read for settings
The file SHALL be read for settings only when a rule on them is enabled, whatever `--paths` says, because its settings apply to the whole store.

#### Scenario: Narrowed paths
- **WHEN** C-02 is enabled and `--paths=app/code/Acme/Shop` is given
- **THEN** C-02 still reports `app/etc/config.php`

#### Scenario: Rule not enabled
- **WHEN** C-02 is not enabled and `app/etc/config.php` switches merging on
- **THEN** no C-02 finding is reported

### Requirement: C-02 JavaScript merging locked on in app/etc/config.php
C-02 SHALL report each `dev/js/merge_files` under `system` → `default`, `system` → `websites` → `<code>` or `system` → `stores` → `<code>` in `app/etc/config.php` whose value is true for PHP, as `isSetFlag()` reads it, at `warning`, on the line of the value. A value that is false for PHP, the same key in any other place, and settings in `app/etc/env.php` or the database SHALL NOT be reported. The rule SHALL be experimental.

#### Scenario: Default scope
- **WHEN** `system` → `default` → `dev` → `js` → `merge_files` is `'1'`
- **THEN** C-02 reports it once, naming the default scope

#### Scenario: Store view
- **WHEN** `system` → `stores` → `en` → `dev` → `js` → `merge_files` is `1`
- **THEN** C-02 reports it, naming the store view `en`

#### Scenario: Switched off or elsewhere
- **WHEN** `merge_files` is `'0'` for the default scope, or `dev` → `js` → `merge_files` is `'1'` outside `system`, or `dev` → `css` → `merge_css_files` is `'1'`
- **THEN** C-02 reports nothing
