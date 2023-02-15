<?php

namespace App\GraphQL\Queries;

final class SessionInfo
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        return [
            'id' => session()->getId(),
        ];
    }
}
