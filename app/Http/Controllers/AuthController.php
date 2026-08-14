<?php

namespace App\Http\Controllers;

use App\Actions\Users\AcceptUserInvitationAction;
use App\Actions\Users\AuthenticateUserAction;
use App\Actions\Users\FindInvitedUserAction;
use App\Actions\Users\LogoutUserAction;
use App\Http\Requests\AcceptUserInvitationRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! AuthenticateUserAction::run($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('AUTH_INVALID_CREDENTIALS')]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request)
    {
        LogoutUserAction::run($request);

        return redirect()->route('login');
    }

    public function invitation(string $token)
    {
        $user = FindInvitedUserAction::run($token);

        abort_unless($user, 404);

        return view('auth.accept-invitation', compact('token', 'user'));
    }

    public function acceptInvitation(AcceptUserInvitationRequest $request, string $token)
    {
        $user = FindInvitedUserAction::run($token);
        abort_unless($user, 404);

        AcceptUserInvitationAction::run($user, $request->validated('password'));

        Auth::login($user);

        $request->session()->regenerate();

        $route = $user->is_admin ? 'dashboard' : 'user.profile';

        return redirect()->route($route)->with('status', __('AUTH_INVITATION_ACCEPTED'));
    }
}
