<?php

namespace  App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ChannelCdn extends Model
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
        return $this->belongsToMany(Channel::class, 'iptv_urls','iptv_cdn_id', 'iptv_channel_id')->using(ChannelUrl::class);
    }


    /**
     *  This function will return always true.
     */
    public function canDelete(){
        return true;
    }

}
