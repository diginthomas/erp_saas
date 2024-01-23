<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Modules\Core\Support\Macros\Filesystem;

class DeepCleanDirectory
{
    /**
     * Deep clean the given directory
     *
     * @param  string  $directory
     */
    public function __invoke($directory, array $except = []): bool
    {
        if (! is_dir($directory)) {
            return false;
        }

        if (substr($directory, strlen($directory) - 1, 1) != '/') {
            $directory .= '/';
        }

        $items = glob($directory.'*', GLOB_MARK);

        foreach ($items as $item) {
            if (is_dir($item)) {
                (new static())($item);
            } elseif (! in_array($item, $except)) {
                unlink($item);
            }
        }

        return @rmdir($directory);
    }
}
