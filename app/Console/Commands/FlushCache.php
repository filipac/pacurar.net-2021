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
        $this->call('cache:clear');
        $this->call('view:clear');

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
            $this->info('W3 Total Cache flushed.');
        } else {
            $this->warn('W3 Total Cache not available.');
        }

        $this->info('All caches cleared.');

        return self::SUCCESS;
    }
}
