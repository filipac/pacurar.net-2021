<?php

namespace App\GraphQL\Directives;

use App\Exceptions\MissingAuthorizationToken;
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
directive @activeWallet on FIELD_DEFINITION
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

        if(!session()->has('authorization')) {
            $errorPool->record(new MissingAuthorizationToken());
            return $fieldValue->setResolver(function () {
                return null;
            });
        }

        $fieldValue->setResolver(
            $resolverProvider->provideResolver($fieldValue)
        );

        return $fieldValue;
    }
}
