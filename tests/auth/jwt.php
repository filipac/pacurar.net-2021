<?php

/** JWT dependency compatibility: no WordPress boot, database, or real credentials. */
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Jwt;
use App\JwtParseResult;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

$app = new Container;
Container::setInstance($app);
$key = str_repeat('fixture-key-', 4);
$app->instance('config', new Repository(['multiversx' => ['jwt_secret' => base64_encode($key)]]));
$checks = 0;
$check = function (bool $condition, string $label) use (&$checks): void {
    if (! $condition) {
        throw new RuntimeException($label);
    }
    $checks++;
};

try {
    $token = Jwt::generateFor('fixture-session', 'fixture-wallet');
    $check($token->claims()->get('jti') === 'fixture-session', 'Immutable builder retains session ID');
    $check($token->claims()->get('uid') === 'fixture-wallet', 'Wallet claim retained');
    $check((new Validator)->validate($token, new SignedWith(new Sha256, InMemory::plainText($key))), 'Generated token signature is valid');
    $check(Jwt::validate($token->toString(), 'fixture-session') === JwtParseResult::ERR_NONE, 'Generated token round trips');
    $check(Jwt::validate($token->toString(), 'wrong-session') === JwtParseResult::ERR_SESSION_ID_MISMATCH, 'Session mismatch rejected');
    $check(Jwt::validate($token->toString(), null) === JwtParseResult::ERR_NONE, 'Optional session remains supported');
    $withoutSession = Jwt::generateFor(null, 'fixture-wallet');
    $check(! $withoutSession->claims()->has('jti'), 'No session claim fabricated');
    $check(Jwt::validate($withoutSession->toString(), null) === JwtParseResult::ERR_NONE, 'Sessionless token round trips');
    $check(Jwt::validate('not-a-token', null) === JwtParseResult::ERR_INVALID, 'Malformed token rejected');
    Carbon::setTestNow(Carbon::now()->subDays(2));
    $expired = Jwt::generateFor('fixture-session', 'fixture-wallet');
    $check(Jwt::validate($expired->toString(), 'fixture-session') === JwtParseResult::ERR_EXPIRED, 'Expired token rejected');
} finally {
    Carbon::setTestNow();
}

echo "$checks JWT compatibility checks passed\n";
