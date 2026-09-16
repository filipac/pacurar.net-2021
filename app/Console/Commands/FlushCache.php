<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlushCache extends Command
{
    protected $signature = 'cache:flush-all';

    protected $description = 'Clear all caches including W3 Total Cache';

    public function handle(): int
    {
        // Laravel caches
        $cacheResult = $this->call('cache:clear');
        $viewResult = $this->call('view:clear');

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
            $this->info('W3 Total Cache flushed.');
        } else {
            $this->warn('W3 Total Cache not available.');
        }

        if ($cacheResult !== self::SUCCESS || $viewResult !== self::SUCCESS) {
            $this->error('Some Laravel caches could not be cleared.');

            return self::FAILURE;
        }

        $this->info('All caches cleared.');

        return self::SUCCESS;
    }
}
