<?php

namespace FelipeMateus\IPTVGatewayPayment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPTVGateway extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code','name','class_model','config_data','active'
    ];

    protected $table = "iptv_gateways_payments";


      /**
     * customer do invoce
     *
     *  @return realation
     *
    */
    public function gateway(){

        return $this->belongsToMany(
            Trop::class,
            'iptv_customer_invoce_gateway',
            'iptv_customer_invoces_id',
            'customer_invoce_gateway_id'
        );
    }


}
