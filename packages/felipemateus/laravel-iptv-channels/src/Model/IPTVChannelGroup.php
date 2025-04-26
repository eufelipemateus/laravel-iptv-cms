<?php

namespace  FelipeMateus\IPTVChannels\Model;

use Illuminate\Database\Eloquent\Model;

class IPTVChannelGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

	protected $table = "iptv_channel_groups";

    /**
     * Get the comments for the blog post.
     */
    public function channels()
    {
        return $this->hasMany('FelipeMateus\IPTVChannels\Model\IPTVChannel');
    }

}
