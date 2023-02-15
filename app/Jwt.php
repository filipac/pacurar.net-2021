<?php

namespace App;

use Carbon\Carbon;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;

class Jwt
{
    public static function validate(string $token, string $sessionId): JwtParseResult
    {
        $parser = new Parser(new JoseEncoder());

        $token = $parser->parse($token);

        $validator = new Validator();

        if (!$validator->validate($token, new IdentifiedBy($sessionId))) {
            return JwtParseResult::ERR_SESSION_ID_MISMATCH;
        }

        if (!$validator->validate($token, new PermittedFor('pacurar-blog'))) {
            return JwtParseResult::ERR_AUDIENCE_MISMATCH;
        }

        if (!$validator->validate($token, new IssuedBy('pacurar-blog'))) {
            return JwtParseResult::ERR_ISSUER_MISMATCH;
        }

        $assert = new StrictValidAt(new SystemClock(new \DateTimeZone('UTC')));

        if (!$validator->validate($token, $assert)) {
            return JwtParseResult::ERR_EXPIRED;
        }

        return JwtParseResult::ERR_NONE;
    }

    public static function generateFor(
        string $sessionId,
        string $address,
    ): Plain
    {
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));
        $algorithm = new Sha256();
        $signingKey = InMemory::plainText(base64_decode(config('multiversx.jwt_secret')));

        $now = Carbon::now();
        return $tokenBuilder
            ->issuedBy('pacurar-blog')
            ->permittedFor('pacurar-blog')
            ->identifiedBy($sessionId)
            ->issuedAt($now->toDateTimeImmutable())
            ->expiresAt($now->copy()->addHours(24)->toDateTimeImmutable())
            ->canOnlyBeUsedAfter($now->toDateTimeImmutable())
            ->withClaim('uid', $address)
            ->getToken($algorithm, $signingKey);
    }
}
