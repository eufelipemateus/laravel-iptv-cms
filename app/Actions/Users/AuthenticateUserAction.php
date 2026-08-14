<?php

namespace App\Actions\Users;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class AuthenticateUserAction
{
    use AsAction;

    /** @param array<string, string> $credentials */
    public function handle(array $credentials, bool $remember): bool
    {
        return Auth::attempt($credentials + [
            'active' => true,
        ], $remember);
    }
}
