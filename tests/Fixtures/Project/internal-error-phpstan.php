<?php

// Behaves like PHPStan with an internal error that is not a dead worker: exit code 1 and the
// error only in "errors". A run with --debug leaves a marker in the project, so a test can tell
// that no single-process run was made for it.
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--configuration=')) {
        foreach (file(substr($arg, strlen('--configuration='))) ?: [] as $line) {
            if (str_starts_with($line, 'parameters: ')) {
                $root = json_decode(substr($line, strlen('parameters: ')), true)['magentoPerf']['projectRoot'];
            }
        }
    }
}
if (in_array('--debug', $argv, true)) {
    touch($root . '/debug-ran');
    exit(255);
}
echo json_encode([
    'totals' => ['errors' => 1, 'file_errors' => 0],
    'files' => [],
    'errors' => ['Internal error: Something went wrong in a rule while analysing file Foo.php'],
]);
exit(1);
