<?php

namespace App\Actions\Users;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class InviteUserAction
{
    use AsAction;

    /** @param array{name: string, email: string} $data */
    public function handle(array $data, bool $isAdmin): User
    {
        $token = Str::random(64);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(64)),
            'is_admin' => $isAdmin,
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        Mail::to($user)->send(new UserInvitationMail($user, $token));

        return $user;
    }
}
