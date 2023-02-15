<?php

namespace App;

class NativeAuthToken
{
    public function decode(string $accessToken): array
    {
        [$address, $body, $signature] = explode('.', $accessToken);

        $parsedAddress = $this->decodeValue($address);
        $parsedBody = $this->decodeValue($body);
        $components = explode('.', $parsedBody);

        $defaultComponents = [
            null,
            null,
            null,
            null
        ];

        [$host, $blockHash, $ttl, $extraInfo] = $components + $defaultComponents;

        ray($parsedAddress, $components, $parsedBody);
    }

    private function decodeValue($str)
    {
        return base64_decode(urldecode($str));
    }
}
