<?php

namespace App\GraphQL\Mutations;

use App\Jwt;
use Carbon\Carbon;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Peerme\Mx\Address;
use Peerme\Mx\SignableMessage;
use Peerme\Mx\Signature;
use Peerme\Mx\UserVerifier;

final class VerifyLogin
{
    /**
     * @param null $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        try {
            $address = Address::fromBech32($args['address']);
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
        $signature = new Signature($args['signature']);
        $token = session()->getId();

        $verifiable = new SignableMessage(
            message: "{$address->bech32()}{$token}{}", // how wallet providers sign login messages
            signature: $signature,
            address: $address,
        );

        return [
            'success' => UserVerifier::fromAddress($address)
                ->verify($verifiable),
            'token' => Jwt::generateFor(
                sessionId: $token,
                address: $address->bech32(),
            )->toString(),
        ];
    }
}
