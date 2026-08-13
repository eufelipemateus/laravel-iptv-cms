<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptUserInvitationAction
{
    use AsAction;

    public function handle(User $user, string $password): User
    {
        $user->update([
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ]);

        return $user;
    }
}
