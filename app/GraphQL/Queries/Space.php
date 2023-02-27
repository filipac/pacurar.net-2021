<?php

namespace App\GraphQL\Queries;

use App\Models\AdSpace;

final class Space
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $space = AdSpace::query()
            ->where('name', $args['spaceName'])
            ->firstOrNew();

        if(!$space->exists) {
            $space->name = $args['spaceName'];
        }

        return $space;
    }
}
