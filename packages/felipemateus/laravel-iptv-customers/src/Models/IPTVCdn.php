<?php

namespace FelipeMateus\IPTVCustomers\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use FelipeMateus\IPTVChannels\Model\IPTVCdn as IPTVCdnParent;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomer;

class IPTVCdn extends IPTVCdnParent {

    /**
     * Costumers to CDN
     */
    public function customers(): HasMany
    {
        return $this->hasMany(IPTVCustomer::class, 'iptv_cdn_id');
    }

    /**
     *  This function verify if cdn can be deleted
     */
    public function canDelete(){
        return self::customers()->count() ? false : true;
    }

}
