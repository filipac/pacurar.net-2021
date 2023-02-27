<?php

namespace App\GraphQL\Queries;

use Peerme\MxLaravel\Multiversx;

final class AcceptedTokens
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $api = Multiversx::api();

        $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getAcceptedTokens', []);

        $tokens = [];
        if($resp->code === 'ok') {
            foreach($resp->data as $token) {
                $tokens[] = base64_decode($token);
            }
        }

        return $tokens;
    }
}
