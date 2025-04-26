<?php

namespace  FelipeMateus\IPTVChannels\Model;

use Illuminate\Database\Eloquent\Model;

use FelipeMateus\IPTVChannels\Model\IPTVCdn;
use Illuminate\Support\Facades\DB;


class IPTVChannel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'number', 'name','logo','radio',
    ];

	protected $table = "iptv_channels";

	/**
     * Get the group that this channel.
     */
    public function group()
    {
        return $this->belongsTo('FelipeMateus\IPTVChannels\Model\IPTVChannelGroup');
    }

    /**
     * get list fucntion
     *
     * @return list
     */
	public function scopeGetListM3u8($query, $cdn_slug){

       return DB::table('iptv_cdns')
        ->join('iptv_urls', 'iptv_urls.iptv_cdn_id', '=', 'iptv_cdns.id')
        ->join('iptv_channels', 'iptv_channels.id', '=', 'iptv_urls.iptv_channel_id')
        ->join('iptv_channel_groups', 'iptv_channel_groups.id', '=', 'iptv_channels.group_id')
        ->select(
            'iptv_channels.number',
            'iptv_channels.name',
            'iptv_channels.logo',
            'iptv_channels.radio',
            DB::raw("iptv_channel_groups.name as group_name"),
            'iptv_urls.url_stream'
        )
        ->where('iptv_cdns.slug',$cdn_slug)
        ->groupBy(
            'iptv_cdns.slug',
            'iptv_channels.number',
            'iptv_channels.name',
            'iptv_channels.logo',
            'iptv_channels.radio',
            'iptv_channel_groups.name',
            'iptv_urls.url_stream',
        )
        ->orderBy(
            'iptv_channels.number'
        )
        ->get();
	}

    /**
     * get list fucntion
     *
     * @return list
     */
	public function scopeGetList($query){
    	return $query->orderBy("radio")->orderBy('number')->get();
    }

	public function setLogoAttribute($image){
		$nameLogo = md5($image->getClientOriginalName()).'.'.$image->getClientOriginalExtension();
		$path = "logos/";
		$destinationPath = public_path('/'.$path);
		$image->move($destinationPath, $nameLogo);
		$this->attributes['logo'] =  $path.$nameLogo;
	}
}
