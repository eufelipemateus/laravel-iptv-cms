<?php

namespace FelipeMateus\IPTVCustomers\Models;

use FelipeMateus\IPTVChannels\Model\IPTVChannel;
use Illuminate\Support\Facades\DB;

class IPTVCustomerChannel extends IPTVChannel
{

    /**
     * get list fucntion
     *
     * @return list
     */
	public function scopeGetCustomerChannelListM3u8($query, $cdn_slug, $customer_id){

        $planQuery = DB::table('iptv_cdns')
         ->join('iptv_urls', 'iptv_urls.iptv_cdn_id', '=', 'iptv_cdns.id')
         ->join('iptv_channels', 'iptv_channels.id', '=', 'iptv_urls.iptv_channel_id')
         ->join('iptv_channel_groups',   'iptv_channels.group_id', '=',  'iptv_channel_groups.id',)
         ->join('iptv_plans', 'iptv_channel_groups.iptv_plan_id', '=', 'iptv_plans.id')
         ->join('iptv_customers', 'iptv_plans.id', '=', 'iptv_customers.iptv_plan_id')
         ->select(
             'iptv_channels.number',
             'iptv_channels.name',
             'iptv_channels.logo',
             'iptv_channels.radio',
             DB::raw("iptv_channel_groups.name as group_name"),
             'iptv_urls.url_stream'
         )
         ->where('iptv_cdns.slug',$cdn_slug)
         ->where('iptv_customers.id', $customer_id)
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
         );

        $planAdditionalQuery = DB::table('iptv_cdns')
         ->join('iptv_urls', 'iptv_urls.iptv_cdn_id', '=', 'iptv_cdns.id')
         ->join('iptv_channels', 'iptv_channels.id', '=', 'iptv_urls.iptv_channel_id')
         ->join('iptv_channel_groups',   'iptv_channels.group_id', '=',  'iptv_channel_groups.id',)
         ->join('iptv_plans', 'iptv_channel_groups.iptv_plan_id', '=', 'iptv_plans.id')
         ->join('iptv_customer_plan_additionals', 'iptv_plans.id','=','iptv_customer_plan_additionals.iptv_plans_id' )
         ->select(
             'iptv_channels.number',
             'iptv_channels.name',
             'iptv_channels.logo',
             'iptv_channels.radio',
             DB::raw("iptv_channel_groups.name as group_name"),
             'iptv_urls.url_stream'
         )
         ->where('iptv_cdns.slug',$cdn_slug)
         ->where('iptv_customer_plan_additionals.iptv_customer_id', $customer_id)
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
         );

        return $planQuery->union($planAdditionalQuery)->get();
     }

}
