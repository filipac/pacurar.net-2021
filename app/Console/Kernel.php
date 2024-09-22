<?php

namespace App\Console;

use App\Jobs\CalculateStreak;
use App\Jobs\RemoveExpiredAdSpaces;
use App\Jobs\UpdateWeb3CommentsName;
use Illuminate\Console\Scheduling\Schedule;
use LaraWelP\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $loadWordpressPlugins = true;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(CalculateStreak::class)->everySixHours();
        $schedule->job(RemoveExpiredAdSpaces::class)->everyTwoHours();
//        $schedule->job(UpdateWeb3CommentsName::class)->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
