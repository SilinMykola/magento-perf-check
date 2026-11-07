# Tasks

## 1. Results that cannot be trusted

- [x] 1.1 Exit code 2 for errors outside any file, in every format, with the report kept and no baseline
- [x] 1.2 Exit code 2 when nothing was analyzed
- [x] 1.3 Fingerprint of the indexed XML as a PHPStan parameter
- [x] 1.4 Generated configuration named after its content
- [x] 1.5 Tests for each, seen failing before the fix

## 2. Discovery and paths

- [x] 2.1 Follow symlinked directories in XML discovery, with a loop guard
- [x] 2.2 Analyze a single `.phtml` path
- [x] 2.3 Normalize and deduplicate paths, reject `..`
- [x] 2.4 Compare `--output` and `--baseline` as resolved paths; check the baseline file before the analysis

## 3. Notices and temp files

- [x] 3.1 Notice summary on stderr for SARIF, Checkstyle, GitHub and `--output`
- [x] 3.2 `generated-code` notice
- [x] 3.3 Private, per-user temp directory

## 4. Rules

- [x] 4.1 P-LOOP-01 scope, with fixtures and rule page
- [x] 4.2 P-COL-01 scope, with fixtures and rule page

## 5. Documentation

- [x] 5.1 Security and temp files in `docs/configuration.md`, security in the README
- [x] 5.2 README precision text
- [x] 5.3 `CHANGELOG.md`
