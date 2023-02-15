<?php

namespace App\GraphQL\Queries;

use App\Jwt;
use App\JwtParseResult;

final class VerifyToken
{
    /**
     * @param null $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        $validateResult = Jwt::validate(
            token: $args['token'],
            sessionId: session()->getId()
        );
        return [
            'success' => $validateResult === JwtParseResult::ERR_NONE,
            'error_code' => $validateResult->value,
        ];
    }
}
