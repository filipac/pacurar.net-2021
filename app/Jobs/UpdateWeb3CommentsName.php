<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Peerme\MxLaravel\Multiversx;
use Peerme\MxProviders\Entities\Account;

class UpdateWeb3CommentsName implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $api = Multiversx::api();

        $cached = [];

        $comments = DB::query()
            ->from('comments')
            ->where('comment_author', 'like', 'erd%')
            ->leftJoin('commentmeta as cm', function ($join) {
                $join->on('comments.comment_ID', '=', 'cm.comment_id')
                    ->where('cm.meta_key', '=', 'web3_address_process');
            })
            ->whereNull('cm.comment_id')
            ->get();

        foreach ($comments as $comm) {
            $address = $comm->comment_author;
            $add = $this->account($address, $api, $cached);
            if ($add instanceof Account && $add->username) {
                DB::table('comments')
                    ->where('comments.comment_ID', $comm->comment_ID)
                    ->update([
                        'comment_author' => $add->username,
                    ]);
            }

            DB::table('commentmeta')
                ->insert([
                    'comment_id' => $comm->comment_ID,
                    'meta_key' => 'web3_address_process',
                    'meta_value' => '1',
                ]);
        }

        if(count($cached) > 0) {
            $prev = get_option('web3_address_cache', []);
            $cached = array_merge($prev, $cached);
            update_option('web3_address_cache', $cached);
        }

    }

    private function account($address, $api, &$cached)
    {
        return $cached[$address] ?? $cached[$address] = $api->accounts()->getByAddress($address);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
