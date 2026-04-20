<?php

namespace App\Domain\IdentityAndAccess\DTOs;

readonly class UpdateProfileDTO
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
    ) {}
}
