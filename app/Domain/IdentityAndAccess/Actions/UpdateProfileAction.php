<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\DTOs\UpdateProfileDTO;
use App\Models\User;

class UpdateProfileAction
{
    public function execute(UpdateProfileDTO $dto): User
    {
        $user = User::query()->findOrFail($dto->userId);

        $user->fill([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
