<?php

namespace App;

class UseFfi
{
    static protected function ffi(): \FFI
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

        return $ffi;
    }

    public static function decode_advertise_space(string $base64): \FFI\Cdata
    {
        return static::ffi()->decode_advertise_space($base64);
    }

    public static function decode_sft_price(string $base64): \FFI\Cdata
    {
        return static::ffi()->decode_sft_price($base64);
    }

    public static function decode_sft_attributes(string $base64): \FFI\Cdata
    {
        return static::ffi()->decode_sft_attributes($base64);
    }
}
