<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\DTOs\RegisterUserDTO;
use App\Models\User;

class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): User
    {
        return User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->passwordHash,
            'role' => $dto->role,
        ]);
    }
}
