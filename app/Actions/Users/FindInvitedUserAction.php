<?php

namespace App\Actions\Users;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class FindInvitedUserAction
{
    use AsAction;

    public function handle(string $token): ?User
    {
        return User::query()
            ->where('invitation_token', hash('sha256', $token))
            ->where('invitation_expires_at', '>', now())
            ->first();
    }
}
