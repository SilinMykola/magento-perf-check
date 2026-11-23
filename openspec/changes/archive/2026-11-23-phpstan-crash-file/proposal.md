# Proposal

## Why

When one PHPStan worker dies, PHPStan drops the findings of every file and the report holds only XML findings. On a private Magento 2.2.3 project a single obfuscated 275 KB helper of a licensed module made a worker reach the 2G memory limit, and every PHP finding of the project was lost: with all rules on, 856 of its 895 findings come from PHPStan. The user cannot tell which file it was: PHPStan's message names none.

## What Changes

- After a run whose JSON output says a worker died (a `Child process error` or the memory limit), PHPStan runs once more in a single process with `--debug`, which prints each file before analyzing it; files that the run named in an internal error are left out of that process, as it would stop on them first. When the process dies, the last file it printed is analyzed alone with the same limit; when that also fails, the file is left out of the analysis with a `phpstan-crash` notice that names it and the reason, and the analysis runs again, as for an unparsable file.
- The file is remembered in the tool's temp directory with the MD5 of its content and the memory limit, so later runs with the same limit leave it out from the start (with the same notice, when it lies in the analyzed paths) and PHPStan's result cache stays valid; a changed file or another limit is searched for again.
- When the single process does not die, dies on a file that analyzes fine alone, or the passes run out, the result stays incomplete as before. A timeout or an internal error is not searched for.
