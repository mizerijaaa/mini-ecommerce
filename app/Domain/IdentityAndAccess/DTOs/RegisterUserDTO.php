<?php

namespace App\Domain\IdentityAndAccess\DTOs;

readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $passwordHash,
        public string $role = 'buyer',
    ) {}
}
