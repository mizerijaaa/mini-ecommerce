<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Domain\IdentityAndAccess\Models\UserFactory as DomainUserFactory;

/**
 * Bridge factory for Breeze scaffolding/tests that reference App\Models\User.
 */
class UserFactory extends DomainUserFactory
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = User::class;
}

