<?php

namespace App\Jobs;

use App\Models\AdSpace;
use App\ValueObjects\AdvertiseSpace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveExpiredAdSpaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $all = AdSpace::get();

        foreach ($all as $space) {
            try {
                $s = AdvertiseSpace::named($space->name);
                if($s->is_new) {
                    $space->delete();
                }
            } catch (\Exception $e) {
                $space->delete();
                continue;
            }
        }
    }
}
