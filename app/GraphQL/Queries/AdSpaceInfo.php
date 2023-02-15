<?php

namespace App\GraphQL\Queries;

use Peerme\Mx\Utils\Decoder;
use Peerme\MxLaravel\Multiversx;

final class AdSpaceInfo
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $api = Multiversx::api();

        $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getSpace', [
            $args['spaceName'],
        ]);

        if($resp->code === 'ok') {
            return $resp->data[0];
        }

        return null;
    }
}
