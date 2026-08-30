<p>{{ __('MAIL_INVITATION_GREETING', ['name' => $user->name]) }}</p>
<p>{{ __('MAIL_INVITATION_INTRO') }}</p>
<p><a href="{{ route('invitation.show', $token) }}">{{ __('MAIL_INVITATION_ACTION') }}</a></p>
<p>{{ __('MAIL_INVITATION_EXPIRATION') }}</p>
