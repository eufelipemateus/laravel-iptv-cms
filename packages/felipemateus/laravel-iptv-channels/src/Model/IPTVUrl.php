<?php

namespace  FelipeMateus\IPTVChannels\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use FelipeMateus\IPTVChannels\Model\IPTVChannel;


class IPTVUrl extends Pivot
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url_stream','iptv_cdn_id','iptv_channel_id'
    ];

	protected $table = "iptv_urls";

     /**
     * The channels that belong to the user.

    public function channels(){
        return $this->belongsToMany(IPTVChannel::class)->withPivot('url');
    }*/

}
