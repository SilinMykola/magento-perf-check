<?php

// Behaves like PHPStan on a project with one file that exhausts the memory limit (Huge.php):
// in parallel mode a worker dies and every finding is lost; with --debug it prints each file
// before analysing it, with Windows line ends, and the process dies on that file; with the file
// left out it reports the finding of the other file. Each --debug run leaves a marker file.
// Markers in the project change it:
// - blame-small: the single process dies on Small.php instead, which alone analyses fine, as when
//   one process has gathered the memory of all files before it;
// - internal-error: Broken.php has an internal error, which a normal run reports in "errors" and on
//   which the single process stops first, unless the file is left out.
$config = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--configuration=')) {
        $config = substr($arg, strlen('--configuration='));
    }
}
$parameters = [];
foreach (file((string) $config) ?: [] as $line) {
    if (str_starts_with($line, 'parameters: ')) {
        $parameters = json_decode(substr($line, strlen('parameters: ')), true);
    }
}
$root = $parameters['magentoPerf']['projectRoot'];
$small = $root . '/app/code/Acme/Demo/Model/Small.php';
$huge = $root . '/app/code/Acme/Demo/Model/Huge.php';
$broken = $root . '/app/code/Acme/Demo/Model/Broken.php';
$excluded = $parameters['excludePaths']['analyse'];
$hugeLeftOut = in_array($huge, $excluded, true);
$blameSmall = is_file($root . '/blame-small');
$internalError = is_file($root . '/internal-error') && !in_array($broken, $excluded, true);
$died = "Child process error: PHPStan process crashed because it reached configured PHP memory limit: 2G\n while running parallel worker";
$finding = [$small => ['errors' => 1, 'messages' => [[
    'message' => '[P-DI-06] ObjectManager::getInstance() is used directly in Acme\Demo\Model\Small.',
    'line' => 7,
    'identifier' => 'magentoPerf.objectManagerUsage',
]]]];

if (in_array('--debug', $argv, true)) {
    touch($root . '/debug-ran');
    if ($internalError) {
        echo $broken, "\r\n";
        fwrite(STDERR, "Internal error: Something went wrong\n");
        exit(1);
    }
    echo $small, "\r\n";
    if (!$hugeLeftOut || $blameSmall) {
        if (!$blameSmall) {
            echo $huge, "\r\n";
        }
        fwrite(STDERR, "PHP Fatal error:  Allowed memory size of 2147483648 bytes exhausted in phar:///phpstan.phar/src/X.php\n#0 trace\nPHPStan process crashed because it reached configured PHP memory limit: 2G\n");
        exit(255);
    }
    exit(0);
}

if ($parameters['paths'] === [$small]) {
    echo json_encode(['totals' => ['errors' => 0, 'file_errors' => 1], 'files' => $finding, 'errors' => []]);
    exit(1);
}

$errors = $internalError ? ['Internal error: Something went wrong while analysing file ' . $broken] : [];
if (!$hugeLeftOut) {
    $errors[] = $died;
    echo json_encode(['totals' => ['errors' => count($errors), 'file_errors' => 0], 'files' => [], 'errors' => $errors]);
    exit(1);
}

echo json_encode(['totals' => ['errors' => count($errors), 'file_errors' => 1], 'files' => $finding, 'errors' => $errors]);
exit(1);
