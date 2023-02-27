<?php

namespace App\GraphQL\Queries;

use Elliptic\EdDSA;
use Elliptic\Utils;
use Peerme\Mx\Address;
use Peerme\Mx\SignableMessage;
use Peerme\Mx\Signature;
use Peerme\Mx\UserVerifier;

final class ValidatePing
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $address = Address::fromBech32($args['address']);
        $signature = $args['signature'];

        $verifiable = new SignableMessage(
            message: 'signComment@1',
            signature: new Signature($signature),
            address: $address,
        );

        $ret = UserVerifier::fromAddress($address)->verify($verifiable);

        ray($address->bech32(), $signature, $ret);

        return $ret;
    }
}
