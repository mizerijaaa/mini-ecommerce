<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Domain\IdentityAndAccess\Models\UserFactory as DomainUserFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Bridge factory for Breeze scaffolding/tests that reference App\Models\User.
 */
class UserFactory extends DomainUserFactory
{
    /** @var class-string<Model> */
    protected $model = User::class;
}
