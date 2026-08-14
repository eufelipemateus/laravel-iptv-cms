<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCdn extends ChannelCdn
{
    /**
     * Costumers to CDN
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'iptv_cdn_id');
    }

    /**
     *  This function verify if cdn can be deleted
     */
    public function canDelete()
    {
        return self::customers()->count() ? false : true;
    }
}
