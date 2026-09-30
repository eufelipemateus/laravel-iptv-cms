<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STORE_DEMO_EMAIL = 'demo@demo.test';

    public const STORE_DEMO_PASSWORD = 'demo';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'is_admin',
        'invitation_token',
        'invitation_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'active' => 'boolean',
        'is_admin' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $user): void {
            if (! app()->hasMacro('isStore') || ! app()->isStore()) {
                return;
            }

            if (! $user->isStoreDemoUser()) {
                return;
            }

            throw ValidationException::withMessages([
                'email' => __('STORE_DEMO_USER_UPDATE_BLOCKED'),
            ]);
        });
    }

    public function isStoreDemoUser(): bool
    {
        return $this->getOriginal('email', $this->email) === self::STORE_DEMO_EMAIL;
    }
}
