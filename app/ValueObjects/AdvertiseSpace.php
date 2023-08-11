<?php

namespace App\ValueObjects;

use App\UseFfi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Peerme\MxLaravel\Multiversx;

readonly class AdvertiseSpace implements Jsonable, Arrayable
{
    public function __construct(
        public string $owner,
        public float $paid_amount,
        public int $paid_until,
        public bool $is_new,
        public string $name,
    ) {}

    public static function named(string $name)
    {
        $api = Multiversx::api();

        $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getSpace', [
            $name
        ]);

        if($resp->code != 'ok') {
            throw new \Exception('Failed to fetch ad space info');
        }

        return self::fromBase64($resp->data[0], $name);
    }

    public static function fromBase64(string $base64Data, string $name): self
    {
        $result = UseFfi::decode_advertise_space($base64Data);

        if ($result->error) {
            throw new \Exception($result->error_message);
        }

        $space = self::fromCData($result->item, $name);

        return $space;
    }

    public static function fromCData(\FFI\Cdata $cdata, string $name) {
        return new self(
            owner: $cdata->owner,
            paid_amount: (float)bcdiv($cdata->paid_amount, bcpow(10, 6), 2),
            paid_until: (int)$cdata->paid_until,
            is_new: $cdata->is_new,
            name: $name,
        );
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'name' => $this->name,
            'owner' => $this->owner,
            'paid_amount' => $this->paid_amount,
            'paid_until' => $this->paid_until,
            'is_new' => $this->is_new,
        ], $options);
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'owner' => $this->owner,
            'paid_amount' => $this->paid_amount,
            'paid_until' => $this->paid_until,
            'is_new' => $this->is_new,
        ];
    }
}
