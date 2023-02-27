<?php

namespace App\GraphQL\Directives;

use App\Exceptions\MissingAuthorizationToken;
use App\Jwt;
use App\JwtParseResult;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\ResolverProvider;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

final class ActiveWalletDirective extends BaseDirective implements FieldResolver
{
    // TODO implement the directive https://lighthouse-php.com/master/custom-directives/getting-started.html

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"A directive that requires an active wallet connection token"
directive @activeWallet(
 tiedToSession: Boolean = true
) on FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Set a field resolver on the FieldValue.
     *
     * This must call $fieldValue->setResolver() before returning
     * the FieldValue.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue  $fieldValue
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function resolveField(FieldValue $fieldValue)
    {
        $resolverProvider = app(ResolverProvider::class);

        $errorPool = app(\Nuwave\Lighthouse\Execution\ErrorPool::class);

        if (!request()->headers->has('authorization')) {
            $errorPool->record(new MissingAuthorizationToken());
            return $fieldValue->setResolver(function () {
                return null;
            });
        }

        $tied = $this->directiveArgValue('tiedToSession') ?? true;

        $validateResult = Jwt::validate(
            token: \Str::of(request()->header('authorization'))->after('Bearer '),
            sessionId: !$tied ? null : session()->getId()
        );

        if ($validateResult !== JwtParseResult::ERR_NONE) {
            $errorPool->record(
                (new MissingAuthorizationToken())->setMessage(
                    match ($validateResult) {
                        JwtParseResult::ERR_EXPIRED => 'Token expired',
                        JwtParseResult::ERR_SESSION_ID_MISMATCH => 'Token is not valid for this session',
                        default => 'Token is invalid',
                    }
                )
            );
            return $fieldValue->setResolver(function () {
                return null;
            });
        }

        ray($validateResult);

        $fieldValue->setResolver(
            $resolverProvider->provideResolver($fieldValue)
        );

        return $fieldValue;
    }
}
