<?php

namespace FelipeMateus\IPTVGatewayPayment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPTVTaxVat extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','porcent','active'
    ];

    protected $table = "iptv_tax_vat";

}
