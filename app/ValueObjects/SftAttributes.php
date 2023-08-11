<?php

namespace App\ValueObjects;

use App\UseFfi;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Peerme\MxLaravel\Multiversx;
use Peerme\MxProviders\Entities\Nft;

class SftAttributes implements Jsonable, Arrayable
{
    public function __construct(
        readonly public string $identifier,
        readonly public int $creation_timestamp,
        readonly public string    $payment_token,
        readonly public int $payment_token_nonce,
        readonly public string $price
    )
    {
    }

    private static array $cachedByIdentifier;

    public static function fetch(string $identifier, bool $bypassCache = false): self
    {
        $api = Multiversx::api();

        if (!isset(self::$cachedByIdentifier)) {
            self::$cachedByIdentifier = [];
        }

        if (!$bypassCache && isset(self::$cachedByIdentifier[$identifier])) {
            return self::$cachedByIdentifier[$identifier];
        }

        try {
            $resp = $api->nfts()->getById($identifier);
        } catch (RequestException $e) {
            throw new \Exception('Failed to fetch SFT');
        }

        if (!($resp instanceof Nft)) {
            throw new \Exception('Failed to fetch SFT');
        }

        self::$cachedByIdentifier[$identifier] = self::fromBase64($resp->attributes, $identifier);

        return self::$cachedByIdentifier[$identifier];
    }

    public static function fromBase64(string $base64, $identifier = '')
    {
        $result = UseFfi::decode_sft_attributes($base64);
        if ($result->error) {
            throw new \Exception($result->error_message);
        }

        return self::fromCData($result->item, $identifier);
    }

    public static function fromCData(\FFI\Cdata $cdata, $identifier = '')
    {
        return new self(
            identifier: $identifier,
            creation_timestamp: $cdata->creation_timestamp,
            payment_token: $cdata->payment_token,
            payment_token_nonce: $cdata->payment_token_nonce,
            price: $cdata->price,
        );
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'amount' => $this->amount,
            'none' => $this->none,
            'token' => $this->token,
        ], $options);
    }

    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'none' => $this->none,
            'token' => $this->token,
        ];
    }
}
