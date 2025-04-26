<?php

namespace  FelipeMateus\IPTVChannels\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use FelipeMateus\IPTVChannels\Model\IPTVUrl;


class IPTVCdn extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug',
    ];

	protected $table = "iptv_cdns";

    /**
     * The channels that belong to the user.
     */
    public function channels(){
        return $this->belongsToMany(IPTVChannel::class, 'iptv_urls','iptv_cdn_id', 'iptv_channel_id')->using(IPTVUrl::class);
    }


    /**
     *  This function will return always true.
     */
    public function canDelete(){
        return true;
    }

}
