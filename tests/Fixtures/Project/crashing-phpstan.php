<?php

// Behaves like PHPStan running out of memory.
fwrite(STDERR, "PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted\n");
exit(255);
