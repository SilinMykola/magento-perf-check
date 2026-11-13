<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Only tests may see these stand-ins for Magento classes. In Composer's autoload-dev they
// also replaced the real classes when the tool itself ran from a checkout, because PHPStan
// loads this package's autoloader: findings then differed from those of an installed package.
foreach (['framework', 'di', 'catalog', 'fpc', 'monolog'] as $stub) {
    require_once __DIR__ . '/PhpStan/Stubs/' . $stub . '.php';
}
