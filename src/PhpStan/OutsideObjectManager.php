<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

/**
 * Code that Magento's object manager never builds and bin/magento never loads: Composer plugins,
 * which Composer runs, and magento/magento-coding-standard. Rules about Magento's runtime
 * do not apply to them.
 */
final class OutsideObjectManager
{
    /** @var array<string, bool> directory => whether code there is outside the object manager */
    private array $outsideByDirectory = [];

    public function contains(string $file): bool
    {
        if (str_contains($file, '/magento/magento-coding-standard/')) {
            return true;
        }
        $dir = dirname($file);
        $visited = [];
        $outside = false;
        while ($dir !== dirname($dir)) {
            if (isset($this->outsideByDirectory[$dir])) {
                $outside = $this->outsideByDirectory[$dir];
                break;
            }
            $visited[] = $dir;
            if (is_file($dir . '/composer.json')) {
                $data = json_decode((string) @file_get_contents($dir . '/composer.json'), true);
                $outside = is_array($data) && ($data['type'] ?? null) === 'composer-plugin';
                break;
            }
            $dir = dirname($dir);
        }
        foreach ($visited as $visitedDir) {
            $this->outsideByDirectory[$visitedDir] = $outside;
        }

        return $outside;
    }
}
