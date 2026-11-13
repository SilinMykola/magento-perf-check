<?php

declare(strict_types=1);

// Monolog\Logger as Monolog 1 has it, with addError(), addInfo() and err(); addWarning() is left out, as in Monolog 2,
// so that the tests cover both a project before 2.4.4 and one on it.
namespace Monolog {
    class Logger implements \Psr\Log\LoggerInterface
    {
        public function addError($message, array $context = []) { return true; }
        public function addInfo($message, array $context = []) { return true; }
        public function err($message, array $context = []) { return true; }
        public function error($message, array $context = []) { }
        public function warning($message, array $context = []) { }
    }
}

namespace Psr\Log {
    interface LoggerInterface
    {
        public function error($message, array $context = []);
        public function warning($message, array $context = []);
    }
}
