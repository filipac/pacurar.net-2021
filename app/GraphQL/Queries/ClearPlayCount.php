<?php

namespace App\GraphQL\Queries;

final class ClearPlayCount
{
    /**
     * @param null    $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        $paddedNonce = $args['nonce'] < 10 ? '0' . $args['nonce'] : $args['nonce'];

        $key = 'played_filip_' . $args['identifier'] . '-' . $paddedNonce;
        cache()->forget($key);

        if (isset($args['guestAddress'])) {
            $keyGuest = 'played_guest_' . $args['identifier'] . '-' . $paddedNonce . '_' . $args['guestAddress'];
            cache()->forget($keyGuest);
        }

        return true;
    }
}
