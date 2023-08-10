<?php

namespace App\GraphQL\Mutations;

use App\DecodeSpaceInfo;
use App\Exceptions\MissingAuthorizationToken;
use App\GraphQL\Queries\AdSpaceInfo;
use App\Jwt;
use App\JwtParseResult;
use App\Models\AdSpace;
use Peerme\MxLaravel\Multiversx;
use Symfony\Component\Process\Process;

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

        $api = Multiversx::api();

        $resp = $api->vm()->query(config('multiversx.ad_contract.address'), 'getSpace', [
            $args['spaceName'],
        ]);

        if ($resp->code === 'ok') {
            $_space = $resp->data[0];

            try {
                $_space = DecodeSpaceInfo::fromBase64($_space, $args['spaceName']);

                if ($_space->owner !== $currentWallet) {
                    throw new MissingAuthorizationToken('Unauthorized');
                }
            } catch (MissingAuthorizationToken $e) {
                throw new MissingAuthorizationToken('Unauthorized');
            } catch (\Exception $e) {
                return null;
            }

        } else {
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
