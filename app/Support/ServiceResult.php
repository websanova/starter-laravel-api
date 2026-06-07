<?php

namespace App\Support;

class ServiceResult
{
    /**
     * @param bool $success
     * @param string|null $error
     * @param mixed $data
     */
    protected function __construct(
        public readonly bool $success,
        public readonly ?string $error = null,
        public readonly mixed $data = null,
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(mixed $data = null): static
    {
        return new static(success: true, data: $data);
    }

    /**
     * Create a failed result with an error key fragment.
     */
    public static function error(string $error): static
    {
        return new static(success: false, error: $error);
    }
}
