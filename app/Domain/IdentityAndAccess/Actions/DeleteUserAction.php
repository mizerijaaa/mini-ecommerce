<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\DTOs\DeleteUserDTO;
use App\Models\User;

class DeleteUserAction
{
    public function execute(DeleteUserDTO $dto): void
    {
        User::query()->whereKey($dto->userId)->delete();
    }
}
