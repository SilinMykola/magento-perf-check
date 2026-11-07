<?php

// Behaves like PHPStan when a worker dies: exit code 1 and valid JSON, with the failure only in "errors".
echo json_encode([
    'totals' => ['errors' => 1, 'file_errors' => 0],
    'files' => [],
    'errors' => ['Child process error (exit code 255): PHP Fatal error: Allowed memory size exhausted'],
]);
exit(1);
