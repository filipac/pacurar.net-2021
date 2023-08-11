<?php

namespace App\ValueObjects;

use App\UseFfi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Peerme\MxLaravel\Multiversx;

class SftPrice implements Jsonable, Arrayable
{
    public function __construct(
        readonly public string $amount,
        readonly public int    $nonce,
        readonly public string $token
    )
    {
    }

    private static array $cachedByNonce;

    public static function fetch(int $nonce, bool $bypassCache = false): self
    {
        $api = Multiversx::api();

        if (!isset(self::$cachedByNonce)) {
            self::$cachedByNonce = [];
        }

        if (!$bypassCache && isset(self::$cachedByNonce[$nonce])) {
            return self::$cachedByNonce[$nonce];
        }

        $resp = $api->vm()->query(config('multiversx.access_contract.address'), 'getSftPrice', [
            $nonce,
        ]);

        if ($resp->code !== 'ok') {
            throw new \Exception('Failed to fetch SFT price');
        }

        self::$cachedByNonce[$nonce] = self::fromBase64($resp->data[0]);

        return self::fromBase64($resp->data[0]);
    }

    public static function fromBase64(string $base64)
    {
        $result = UseFfi::decode_sft_price($base64);
        if ($result->error) {
            throw new \Exception($result->error_message);
        }

        return self::fromCData($result->item);
    }

    public static function fromCData(\FFI\Cdata $cdata)
    {
        return new self(
            amount: (string)$cdata->amount,
            nonce: $cdata->nonce,
            token: $cdata->token,
        );
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'amount' => $this->amount,
            'none' => $this->nonce,
            'token' => $this->token,
        ], $options);
    }

    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'none' => $this->nonce,
            'token' => $this->token,
        ];
    }
}
