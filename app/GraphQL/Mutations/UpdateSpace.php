<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\MissingAuthorizationToken;
use App\Jwt;
use App\Models\AdSpace;
use App\ValueObjects\AdvertiseSpace;

final class UpdateSpace
{
    /**
     * @param null $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        $currentWallet = Jwt::getRequiredWallet();
        $space = AdSpace::query()
            ->where('name', $args['spaceName'])
            ->first();

        try {
            $_space = AdvertiseSpace::named($args['spaceName']);

            if ($_space->owner !== $currentWallet) {
                throw new MissingAuthorizationToken('Unauthorized');
            }

        } catch (MissingAuthorizationToken $e) {
            throw new MissingAuthorizationToken('Unauthorized');
        } catch (\Exception $e) {
            return null;
        }

        if (!$space) {
            $space = new AdSpace();
            $space->name = $args['spaceName'];
        }

        $space->content = $args['content'];
        $space->last_modified_by = $currentWallet;
        $space->save();

        $s = $space->fresh();

        // call the ad space info to cache the new data
//        (new AdSpaceInfo)(null, ['spaceName' => $args['spaceName'], 'initial' => true]);

        return $s;
    }
}
