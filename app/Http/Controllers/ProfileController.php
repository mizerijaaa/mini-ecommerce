<?php

namespace App\Http\Controllers;

use App\Domain\IdentityAndAccess\Actions\DeleteUserAction;
use App\Domain\IdentityAndAccess\Actions\UpdateProfileAction;
use App\Domain\IdentityAndAccess\DTOs\DeleteUserDTO;
use App\Domain\IdentityAndAccess\DTOs\UpdateProfileDTO;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        app(UpdateProfileAction::class)->execute(new UpdateProfileDTO(
            userId: (string) $request->user()->id,
            name: (string) $validated['name'],
            email: (string) $validated['email'],
        ));

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        app(DeleteUserAction::class)->execute(new DeleteUserDTO(
            userId: (string) $user->id,
        ));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
