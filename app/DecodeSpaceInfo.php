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
     *
     * @return mixed
     * @throws \Exception
     */
    public static function fromBase64($base64Data)
    {
        $isMacos       = stripos(PHP_OS, 'DAR') !== false;
        $uname         = php_uname('a');
        $isArm64       = stripos($uname, 'aarch64') !== false || stripos($uname, 'arm64') !== false;
        $decoderBinary = match (true) {
            $isMacos => 'decode_struct-macos-arm64',
            default => match(true) {
                $isArm64 => 'decode_struct-aarch64',
                default => 'decode_struct-x86_64',
            },
        };
        $process       = resource_path('js/struct-decoder-rust/' . $decoderBinary);

        $prc = new Process([$process, $base64Data]);
        $prc->run();

        if ($prc->isSuccessful()) {
            $base64Data = json_decode($prc->getOutput(), true);

//            $base64Data['owner']       = $base64Data['owner'];
            $base64Data['paid_amount'] = (float)bcdiv($base64Data['paid_amount'], bcpow(10, 6), 2);
            $base64Data['paid_until']  = (int)$base64Data['paid_until'];

            return $base64Data;

        } else {
            throw new \Exception($prc->getErrorOutput());
        }
    }
}
