<?php

// Reports nothing and appends the --configuration file it was given to MPC_TEST_RECORD.
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--configuration=')) {
        file_put_contents((string) getenv('MPC_TEST_RECORD'), substr($argument, 16) . "\n", FILE_APPEND);
    }
}
echo json_encode(['totals' => ['errors' => 0, 'file_errors' => 0], 'files' => [], 'errors' => []]);
