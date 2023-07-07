<?php

namespace App\GraphQL\Queries;

use Peerme\MxLaravel\Multiversx;

final class TimesPlayedNft
{
    /**
     * @param null    $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        $api = Multiversx::api();

        $paddedNonce = $args['nonce'] < 10 ? '0' . $args['nonce'] : $args['nonce'];

        $key = 'played_filip_' . config('multiversx.counter_contract.nft') . '-' . $paddedNonce;
        $keyGuest = 'played_guest_' . config('multiversx.counter_contract.nft') . '-' . $paddedNonce . '_' . $args['guestAddress'];

        $filip = cache()->remember($key, now()->addMinutes(30), function () use ($api, $args) {

            $resp = $api->vm()->int(config('multiversx.counter_contract.address'), 'getTimesPlayed', [
                config('multiversx.counter_contract.nft'),
                $args['nonce'],
                config('multiversx.counter_contract.owner'),
            ]);
            return (int)$resp->data;
        });

        $guest = null;

        if (isset($args['guestAddress'])) {
            if ($args['guestAddress'] == config('multiversx.counter_contract.owner')) {
                $guest = $filip;
            } else {
                $guest = cache()->remember($keyGuest, now()->addMinutes(30), function () use
                (
                    $api,
                    $args
                ) {
                    $resp = $api->vm()->int(config('multiversx.counter_contract.address'), 'getTimesPlayed', [
                        config('multiversx.counter_contract.nft'),
                        $args['nonce'],
                        $args['guestAddress'],
                    ]);
                    return (int)$resp->data;
                });
            }
        }

        return [
            'nonce'           => $args['nonce'],
            'played_by_filip' => $filip,
            'played_by_you'   => $guest,
        ];
    }
}
