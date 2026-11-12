<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;

/**
 * C-02: Merge JavaScript Files (dev/js/merge_files) switched on in the system section of app/etc/config.php.
 */
final class MergeJsFilesRule
{
    public const ID = 'C-02';

    public const FILE = 'app/etc/config.php';

    /**
     * @return list<Finding>
     */
    public function check(string $projectRoot): array
    {
        $findings = [];
        foreach (ConfigPhpValues::read($projectRoot . '/' . self::FILE) as $value) {
            $scope = self::scope($value['path']);
            // Core reads the flag with isSetFlag(), which casts the value to bool.
            if ($scope === null || !(bool) $value['value']) {
                continue;
            }
            $findings[] = new Finding(
                self::ID,
                Severity::Warning,
                self::FILE,
                $value['line'],
                sprintf(
                    '[%s] Merge JavaScript Files (dev/js/merge_files) is on for %s. Adobe does not recommend it: it was made for '
                    . 'synchronous scripts in the page head, can break RequireJS and bundling, and gives no gain with HTTP/2. Set it to 0.',
                    self::ID,
                    $scope,
                ),
            );
        }

        return $findings;
    }

    /**
     * @param list<string> $path
     */
    private static function scope(array $path): ?string
    {
        if (array_slice($path, -3) !== ['dev', 'js', 'merge_files'] || ($path[0] ?? null) !== 'system') {
            return null;
        }
        if (count($path) === 5 && $path[1] === 'default') {
            return 'the default scope';
        }
        if (count($path) === 6 && $path[1] === 'websites') {
            return sprintf('the website "%s"', $path[2]);
        }
        if (count($path) === 6 && $path[1] === 'stores') {
            return sprintf('the store view "%s"', $path[2]);
        }

        return null;
    }
}
