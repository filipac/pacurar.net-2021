<?php

namespace App\GraphQL\Queries;

use App\DecodeSpaceInfo;
use App\ValueObjects\AdvertiseSpace;
use Peerme\Mx\Utils\Decoder;
use Peerme\MxLaravel\Multiversx;

final class AdSpaceInfo
{
    /**
     * @param null $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        $api = Multiversx::api();

        $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getSpace', [
            $args['spaceName'],
        ]);

        if (!isset($args['initial'])) {
            cache()->forget('ad-space-info-' . $args['spaceName']);
        }

        if ($resp->code === 'ok') {
            try {
                $space = DecodeSpaceInfo::fromBase64($resp->data[0], $args['spaceName'])->toArray();

                cache()->put('ad-space-info-' . $args['spaceName'], $space, now()->addMinutes(5));

                return $space;
            } catch (\Exception $e) {
            ray($e);
            }
        }
        return (new AdvertiseSpace(
            owner: '0x0000000',
            paid_amount: 0,
            paid_until: 0,
            is_new: true,
            name: ''
        ));
    }
}
