<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPTVTaxVat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'porcent',
        'active',
    ];

    protected $table = 'iptv_tax_vat';
}
