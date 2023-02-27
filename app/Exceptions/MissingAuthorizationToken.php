<?php

namespace App\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class MissingAuthorizationToken extends Exception implements ClientAware, ProvidesExtensions
{
    public const CATEGORY = 'authorization';

    private ?string $msg;

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory()
    {
        return self::CATEGORY;
    }

    public function setMessage(?string $message): self
    {
        $this->msg = $message;

        return $this;
    }

    public function getExtensions(): array
    {
        return [
            'message' => isset($this->msg) ? $this->msg : 'Missing authorization token',
        ];
    }
}
