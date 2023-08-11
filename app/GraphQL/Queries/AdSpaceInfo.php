<?php

namespace App\GraphQL\Queries;

use App\ValueObjects\AdvertiseSpace;

final class AdSpaceInfo
{
    /**
     * @param null $_
     * @param array{} $args
     */
    public function __invoke($_, array $args)
    {
        if (!isset($args['initial'])) {
            cache()->forget('ad-space-info-' . $args['spaceName']);
        }

        try {
            $value = AdvertiseSpace::named($args['spaceName'])->toArray();
            cache()->put('ad-space-info-' . $args['spaceName'], $value, now()->addMinutes(5));
            return $value;
        } catch (\Exception $e) {
            return (new AdvertiseSpace(
                owner: '0x0000000',
                paid_amount: 0,
                paid_until: 0,
                is_new: true,
                name: ''
            ));
        }
    }
}
