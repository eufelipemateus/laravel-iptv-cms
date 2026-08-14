<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'iptv_plan_id',
        'iptv_cdn_id',
        'active',
        'due_day',
        'industry',
        'address',
        'phone',
        'email',
        'tax_no',
        'auth_token_expires_at',
    ];

    protected $casts = [
        'auth_token_last_used_at' => 'datetime',
        'auth_token_expires_at' => 'datetime',
        'auth_token_revoked_at' => 'datetime',
    ];

    protected $table = 'iptv_customers';

    public function getPersonalUrlAttribute()
    {
        $cdn = $this->cdn()->first();

        if (! $cdn instanceof ChannelCdn) {
            return '';
        }

        return route('client-playlist', ['slug' => $cdn->slug]);
    }

    public function issueAuthToken(?DateTimeInterface $expiresAt = null): string
    {
        $tokenId = (string) Str::ulid();
        $secret = Str::random(64);

        $this->forceFill([
            'auth_token_id' => $tokenId,
            'auth_token_hash' => Hash::make($secret),
            'auth_token_last_used_at' => null,
            'auth_token_expires_at' => $expiresAt,
            'auth_token_revoked_at' => null,
        ])->save();

        return $tokenId.'.'.$secret;
    }

    public function revokeAuthToken(): void
    {
        $this->forceFill([
            'auth_token_revoked_at' => now(),
        ])->save();
    }

    public function canUseAuthToken(string $secret): bool
    {
        if (! is_string($this->auth_token_hash) || $this->auth_token_hash === '') {
            return false;
        }

        if ($this->auth_token_revoked_at !== null) {
            return false;
        }

        if ($this->auth_token_expires_at !== null && $this->auth_token_expires_at->isPast()) {
            return false;
        }

        return Hash::check($secret, $this->auth_token_hash);
    }

    public function markAuthTokenUsed(?Carbon $usedAt = null): void
    {
        $this->forceFill([
            'auth_token_last_used_at' => $usedAt ?? now(),
        ])->save();
    }

    /**
     * Get the plan for the blog post.
     */
    public function plan()
    {
        return $this->belongsTo(CustomerPlan::class, 'iptv_plan_id');
    }

    /**
     * The plans additional that belong to the customers.
     */
    public function plans_additional()
    {
        return $this->belongsToMany(CustomerPlan::class, 'iptv_customer_plan_additionals', 'iptv_customer_id', 'iptv_plans_id');
    }

    /**
     * get list fucntion
     *
     * @return list
     */
    public function scopeGetList($query)
    {
        return $query->orderBy('name')->get();
    }

    /**
     * Get the cdn for the customer.
     */
    public function cdn()
    {
        return $this->belongsTo(ChannelCdn::class, 'iptv_cdn_id');
    }

    /**
     * Plan Additional List
     */
    public function planAditionalList()
    {
        $exclude = $this->plans_additional()->pluck('iptv_plans_id');

        return CustomerPlan::where('active', 1)->where('additional', 1)->whereNotIn('id', $exclude)->get();
    }

    /*
     * Customer Invoces List
     */
    public function customer_invoce()
    {
        return $this->hasMany(CustomerInvoce::class, 'iptv_customer_id');
    }

    /**
     * Get  defeated
     *
     * @param  string  $value
     * @return bool
     */
    public function getDefeatedAttribute()
    {

        $now = Carbon::now();
        $first_day_this_month = $now->copy()->startOfMonth()->toDateString();
        $last_day_this_month = $now->copy()->endOfMonth()->toDateString();

        $this_month_deafeted = $this->customer_invoce()
            ->whereBetween('duedate_at', [$first_day_this_month, $last_day_this_month])
            ->whereNull('payment_at')
            ->whereNull('canceled_at')
            ->count();

        $before_months = $this->customer_invoce()->where('duedate_at', '<', $first_day_this_month)
            ->whereNull('payment_at')
            ->whereNull('canceled_at')
            ->count();

        if ($this_month_deafeted || $before_months) {
            return true;
        }

        return false;
    }
}
