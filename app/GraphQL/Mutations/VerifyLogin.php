<?php

namespace App\GraphQL\Mutations;

use App\Jwt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
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

        $getDefaultToken = function() {
            $cookies = request()->cookies->all();
            if(array_key_exists('login_token', $cookies)) {
                return $cookies['login_token'];
            }
            return session()->getId();
        };

        $signature = new Signature($args['signature']);
        $token = $args['token'] ?? $getDefaultToken();
        $orin_token = $token;

        $verifiable = new SignableMessage(
            message: "{$address->bech32()}{$token}{}", // how wallet providers sign login messages
            signature: $signature,
            address: $address,
        );


        $verified = UserVerifier::fromAddress($address)
                ->verify($verifiable);

        if($verified) {
            $tokenParts = explode('.', $orin_token);
            $last = array_pop($tokenParts);
            // encoded in js with :  str.replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, ""); , so we need to reverse it
            $last = str_replace('-', '+', $last);
            $last = str_replace('_', '/', $last);
            try {
                $base = base64_decode($last);
                $parsed = \GuzzleHttp\json_decode($base, true);
                if(!array_key_exists('sessionId', $parsed)) {
                    $verified = false;
                } else {
                    $_token = $parsed['sessionId'];
                    if($_token != session()->getId()) {
                        $verified = false;
                    }
                }
            } catch (\Exception|\Error $e) {

            }
//            $last = base64_decode($last);
//            ray($verified, $last);
        }

        $token = $verified ? Jwt::generateFor(
            sessionId: isset($args['offline']) && $args['offline'] ? null : $_token,
            address: $address->bech32(),
        )->toString() : null;

        if($verified) {
            Cookie::queue('blog_token', $token);
        }

        \Session::flash('temp_token', !isset($args['offline']) ? $token : null);

        return [
            'success' => $verified,
            'token' => isset($args['offline']) ? $token : null,
        ];
    }

    /*
     encodeValue(str: string) {
    return this.escape(Buffer.from(str, "utf8").toString("base64"));
  }

  private escape(str: string) {
    return str.replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, "");
  }
     */
}
