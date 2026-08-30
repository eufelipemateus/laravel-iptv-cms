<?php

namespace App\Actions\Users;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserAction
{
    use AsAction;

    /** @param array{name: string, email: string} $data */
    public function handle(User $user, User $actor, array $data, bool $isAdmin, bool $isActive): User
    {
        $attributes = ['name' => $data['name'], 'email' => $data['email']];

        if ($user->isNot($actor)) {
            $attributes['is_admin'] = $isAdmin;
        }

        if (! $user->is_admin) {
            $attributes['active'] = $isActive;
        }

        $user->update($attributes);

        return $user;
    }
}
