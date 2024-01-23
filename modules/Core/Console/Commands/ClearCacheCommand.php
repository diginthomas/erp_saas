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

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the application cache.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): void
    {
        $filesystem->deepCleanDirectory(
            $this->laravel['config']->get('dompdf.options.font_cache')
        );

        foreach ([
            'optimize:clear', 'html-purifier:clear', 'modelCache:clear',
        ] as $command) {
            $this->call($command);
        }
    }
}
