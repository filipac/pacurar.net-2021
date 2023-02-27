<?php

namespace App\Jobs;

use App\DecodeSpaceInfo;
use App\Models\AdSpace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Peerme\MxLaravel\Multiversx;

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
            $api = Multiversx::api();

            $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getSpace', [
                $space->name,
            ]);

            if ($resp->code === 'ok') {
                try {
                    $info = DecodeSpaceInfo::fromBase64($resp->data[0]);
                    ray($info);
                    if ($info['is_new']) {
                        $space->delete();
                    }
                } catch (\Exception $e) {
//                    $space->delete();
                    continue;
                }
            }
        }
    }
}
