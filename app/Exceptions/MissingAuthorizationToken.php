<?php

namespace App\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class MissingAuthorizationToken extends Exception implements RendersErrorsExtensions
{
    const CATEGORY = 'authorization';

    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return self::CATEGORY;
    }

    public function extensionsContent(): array
    {
        return [
            'message' => 'Missing authorization token',
        ];
    }
}
