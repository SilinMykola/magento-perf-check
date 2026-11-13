# Proposal

## Why

A contributor refused to send a calibration report because the tool's JSON report shows file paths; another works on several projects under NDAs. The guide asked people to copy counts out of the report by hand, which left them looking at a file full of paths and module names.

## What Changes

- `resources/calibration-summary.php` makes, from one or more JSON reports, a file to send (counts per rule, notices per source, run time, and a verdict table of up to 30 findings per rule with row number and rule ID only) and a local file with the paths, which stays with the contributor. Several reports give one combined file.
- The calibration guide uses it, and says what an NDA-bound contributor sends.
