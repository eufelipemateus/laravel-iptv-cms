<?php

namespace App\Http\Controllers;

use App\Actions\Users\InviteUserAction;
use App\Actions\Users\ListUsersAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Requests\InviteUserRequest;
use App\Http\Requests\ListUsersRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private function isStoreMode(): bool
    {
        return app()->hasMacro('isStore') && app()->isStore();
    }

    public function list(ListUsersRequest $request)
    {
        $search = trim((string) $request->query('search'));
        $users = ListUsersAction::run($search);

        if ($request->expectsJson()) {
            return UserResource::collection($users);
        }

        return view('users.list', compact('users', 'search'));
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        return view('users.profile', compact('user'));
    }

    public function show(User $user)
    {
        if (request()->expectsJson()) {
            return new UserResource($user);
        }

        return view('users.profile', compact('user'));
    }

    public function inviteForm()
    {
        if ($this->isStoreMode()) {
            return redirect()->route('list_user')->with('status', __('USERS_STORE_READ_ONLY'));
        }

        return view('users.invite');
    }

    public function invite(InviteUserRequest $request)
    {
        if ($this->isStoreMode()) {
            return redirect()->route('list_user')->with('status', __('USERS_STORE_READ_ONLY'));
        }

        $data = $request->validated();

        $user = InviteUserAction::run($data, $request->boolean('is_admin'));

        if ($request->expectsJson()) {
            return (new UserResource($user))->response()->setStatusCode(201);
        }

        return redirect()->route('users.invite')->with('status', __('USERS_INVITATION_SENT', ['email' => $user->email]));
    }

    public function edit(User $user)
    {
        if ($this->isStoreMode()) {
            return redirect()->route('list_user')->with('status', __('USERS_STORE_READ_ONLY'));
        }

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        if ($this->isStoreMode()) {
            return redirect()->route('list_user')->with('status', __('USERS_STORE_READ_ONLY'));
        }

        $data = $request->validated();

        $user = UpdateUserAction::run(
            $user,
            $request->user(),
            $data,
            $request->boolean('is_admin'),
            $request->boolean('active'),
        );

        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        return redirect()->route('list_user')->with('status', __('USERS_UPDATED'));
    }
}
