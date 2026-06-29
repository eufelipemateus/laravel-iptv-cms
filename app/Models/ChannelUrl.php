<?php

namespace  App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;


class ChannelUrl extends Pivot
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
