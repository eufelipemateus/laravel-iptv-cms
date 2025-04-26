<?php

namespace FelipeMateus\IPTVCustomers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVTaxVat;

class IPTVPlan extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'price', 'active', 'additional','iptv_tax_vat_id'
    ];

    protected $table = "iptv_plans";

     /**
     * Get the groups for the iptv plan.
     */
    public function groups()
    {
        return $this->hasMany(IPTVPlanGroup::class, 'iptv_plan_id');
    }


    public function groupsList(){
        $exclude  = $this->groups()->pluck('id');
        return IPTVPlanGroup::whereNotIn('id', $exclude)->get();
    }

    static public function activePlanList(){
        return self::where("active",1)->where("additional",0)->get();
    }

    public function tax_vat (){
        return $this->belongsTo(IPTVTaxVat::class, 'iptv_tax_vat_id');
    }
}
