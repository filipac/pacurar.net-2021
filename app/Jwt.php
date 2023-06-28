<?php

namespace App;

use App\Exceptions\MissingAuthorizationToken;
use Carbon\Carbon;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;

class Jwt
{
    public static function validate(string $token, ?string $sessionId): JwtParseResult
    {
        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($token);
        } catch (\Exception $e) {
            return JwtParseResult::ERR_INVALID;
        }
        /**
         * @var Plain $token
         */

        $validator = new Validator();

        if (isset($sessionId)) {
            $tokenSessionId = $token->claims()->get(RegisteredClaims::ID);

            if ($tokenSessionId && !$validator->validate($token, new IdentifiedBy($sessionId))) {
                return JwtParseResult::ERR_SESSION_ID_MISMATCH;
            }
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
        ?string $sessionId,
        string $address,
    ): Plain {
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));
        $algorithm = new Sha256();
        $signingKey = InMemory::plainText(base64_decode(config('multiversx.jwt_secret')));

        $now = Carbon::now();

        $builder = $tokenBuilder
            ->issuedBy('pacurar-blog')
            ->permittedFor('pacurar-blog')
            ->issuedAt($now->toDateTimeImmutable())
            ->expiresAt($now->copy()->addHours(24)->toDateTimeImmutable())
            ->canOnlyBeUsedAfter($now->toDateTimeImmutable())
            ->withClaim('uid', $address);

        if (isset($sessionId)) {
            $builder->identifiedBy($sessionId);
        }

        return $builder
            ->getToken($algorithm, $signingKey);
    }

    public static function getToken(): ?string
    {
        $val = request()->header('Authorization');

        $isHttpCookieOnly = false;

        if(!$val) {
            $val = request()->cookie('blog_token');
            $isHttpCookieOnly = true;
        }

        if(is_array($val)) {
            $val = $val[0];
        }

        if(!$val || (!$isHttpCookieOnly && str_contains($val, 'Bearer') === false)) {
            return null;
        }

        $token = $isHttpCookieOnly ? $val : str_replace('Bearer ', '', $val);

        return $token;
    }

    public static function getWalletToken()
    {
        $token = self::getToken();

        if(!$token) {
            return null;
        }

        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($token);
        } catch (\Exception $e) {
            return null;
        }

        return $token;
    }

    public static function getCurrentWallet()
    {
        $token = self::getWalletToken();

        if(!$token) {
            return null;
        }

        $currentWallet = $token->claims()->get('uid');

        return $currentWallet;
    }

    public static function getRequiredWallet()
    {
        $token = static::getToken();
        $currentWallet = static::getCurrentWallet();

        if ($currentWallet === null || $token === null) {
            throw new MissingAuthorizationToken('Unauthorized');
        }

        $validate = static::validate($token, null);

        if($validate !== JwtParseResult::ERR_NONE) {
            throw new MissingAuthorizationToken('Unauthorized');
        }

        return $currentWallet;
    }
}
