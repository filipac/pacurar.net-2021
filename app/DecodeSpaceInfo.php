<?php

namespace App;

use App\ValueObjects\AdvertiseSpace;
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
     * @return AdvertiseSpace
     * @throws \Exception
     */
    public static function fromBase64(string $base64Data, string $name): AdvertiseSpace
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

        $result = $ffi->decode_advertise_space($base64Data);
        if ($result->error) {
            throw new \Exception($result->error_message);
        }

        $space = AdvertiseSpace::fromCData($result->item, $name);

        return $space;
    }
}
