<?php

namespace App\Domain\IdentityAndAccess\DTOs;

readonly class DeleteUserDTO
{
    public function __construct(
        public string $userId,
    ) {}
}
