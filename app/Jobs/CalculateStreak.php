<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateStreak implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $args = [
            'orderby'          => 'date',
            'status'           => 'approve',
            'order'            => 'DESC',
            'post_type'        => 'post',
            'post_status'      => ['publish', 'private'],
            'date_query'       => [
                [
                    'after' => '100 days ago',
                ],
            ],
            'posts_per_page'   => -1,
            'suppress_filters' => true,
        ];

        $query = new \WP_Query($args);

        $posts = $query->posts;

        $all = collect($posts)
            ->map(fn($post) => (object)['id' => $post->ID, 'date' => Carbon::parse($post->post_date, 'Europe/Bucharest')->endOfDay()])
            ->sortBy->date;

        $streak    = 0;
        $streaks   = [0];
        $startedOn = 0;
        $last      = null;

        foreach ($all as $p) {
            if ($last) {
                if (!$p->date->isSameDay($last->date) && $p->date->diffInDays($last->date) <= 1) {
                    if (!$startedOn) {
                        $startedOn = $last->date->format('U');
                    }
                    if($streak == 0) {
                        $streak = 1;
                    }
                    $streak += 1;
                } else {
                    $startedOn = 0;
                    $streaks[] = $streak;
                    $streak    = 0;
                }
            }
            $last = $p;
        }
        $streaks[] = $streak;

        if($last) {
            if(!$last->date->isToday() && !$last->date->isYesterday()) {
                $streak = 0;
            }
        }

        $streaks[] = $streak;

        update_option('current_daily_streak_100d', $streak);
        update_option('current_daily_streak_100d_started', $startedOn);
        update_option('best_daily_streak_100d', max($streaks));
    }
}
