<?php

namespace App;

use Symfony\Component\Process\Process;

class DecodeSpaceInfo
{
    private static array $struc = [
        ['name' => 'owner', 'type' => 'Address'],
        ['name' => 'paid_amount', 'type' => 'BigUint'],
        ['name' => 'paid_until', 'type' => 'BigUint'],
        ['name' => 'is_new', 'type' => 'bool'],
    ];

    /**
     * @param $base64Data
     * @return mixed
     * @throws \Exception
     */
    public static function fromBase64($base64Data)
    {
        $isMacos = stripos(PHP_OS, 'DAR') !== false;
        $decoderBinary = match (true) {
            $isMacos => 'struct-decoder-macos-arm64',
            default => 'struct-decoder-linux-x64',
        };
        $process = resource_path('js/' . $decoderBinary);

        // [{"name":"owner","type":"Address"},{"name":"paid_amount","type":"BigUint"},{"name":"paid_until","type":"BigUint"},{"name":"is_new","type":"bool"}]

        $prc = new Process([$process, 'AdvertiseSpace', json_encode(static::$struc), $base64Data]);
        $prc->run();

        if ($prc->isSuccessful()) {
            $base64Data = json_decode($prc->getOutput(), true);

            $base64Data['owner'] = $base64Data['owner']['bech32'];
            $base64Data['paid_amount'] = (float) bcdiv($base64Data['paid_amount'], bcpow(10, 6), 2);
            $base64Data['paid_until'] = (int)$base64Data['paid_until'];

            return $base64Data;

        } else {
            throw new \Exception($prc->getErrorOutput());
        }
    }
}
