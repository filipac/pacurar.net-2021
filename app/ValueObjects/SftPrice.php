<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Peerme\MxLaravel\Multiversx;

class SftPrice implements Jsonable, Arrayable
{
    public function __construct(
        readonly public string $amount,
        readonly public int    $none,
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
        static $header, $ffi;

        if (!isset($header)) {
            $header = file_get_contents(base_path('decoder-ffi/def.h'));
        }

        if (!isset($ffi)) {
            $isMacos = stripos(PHP_OS, 'DAR') !== false;
            $uname = php_uname('a');
            $isArm64 = stripos($uname, 'aarch64') !== false || stripos($uname, 'arm64') !== false;
            $decoderCCode = match (true) {
                $isMacos => 'libdecode_struct.dylib',
                default => match (true) {
                    $isArm64 => 'libdecode_struct_aarch.so',
                    default => 'libdecode_struct_x86_64.so',
                },
            };

            $ffi = \FFI::cdef($header, base_path('decoder-ffi/' . $decoderCCode));
        }

        $result = $ffi->decode_sft_price($base64);
        if ($result->error) {
            throw new \Exception($result->error_message);
        }

        return self::fromCData($result->item);
    }

    public static function fromCData(\FFI\Cdata $cdata)
    {
        return new self(
            amount: (string)$cdata->amount,
            none: $cdata->nonce,
            token: $cdata->token,
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
