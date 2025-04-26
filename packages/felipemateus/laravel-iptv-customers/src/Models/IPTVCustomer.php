<?php

namespace FelipeMateus\IPTVCustomers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use FelipeMateus\IPTVCustomers\Models\IPTVCdn;

class IPTVCustomer extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'hash_acess',
        'iptv_plan_id',
        'iptv_cdn_id',
        'active',
        'due_day',
        'industry',
        'address',
        'phone',
        'email',
        'tax_no'
    ];

    protected $table = "iptv_customers";


    public function getPersonalUrlAttribute(){

        $cdn =  IPTVCdn::findOrFail($this->iptv_cdn_id);


        return http_build_url(route("client-playlist",['slug'=>$cdn->slug]),
            array(
                "user" => $this->username,
                "pass" => $this->hash_acess,
            )
        );

    }

    /**
     * Get the plan for the blog post.
     */
    public function plan()
    {
        return $this->belongsTo(IPTVPlan::class, 'iptv_plan_id');
    }

    /**
     * The plans additional that belong to the customers.
     */
    public function plans_additional()
    {
        return $this->belongsToMany(IPTVPlan::class,'iptv_customer_plan_additionals','iptv_customer_id', 'iptv_plans_id');
    }

    /**
     * get list fucntion
     *
     * @return list
     */
	public function scopeGetList($query){
        return $query->orderBy("name")->get();
    }

    /**
     * Get the cdn for the customer.
     */
    public function cdn()
    {
        return $this->belongsTo(IPTVCdn::class, 'iptv_cdn_id');
    }


    /**
     * Plan Additional List
     */
    public function planAditionalList()
    {
        $exclude  = $this->plans_additional()->pluck('iptv_plans_id');

        return IPTVPlan::where('active', 1)->where('additional', 1)->whereNotIn('id', $exclude)->get();
    }


    /*
     * Customer Invoces List
     */
    public function customer_invoce(){
        return $this->hasMany(IPTVCustomerInvoce::class,  'iptv_customer_id');
    }

    /**
     * Get  defeated
     *
     * @param  string  $value
     * @return boolean
     */
    public function getDefeatedAttribute(){

        $first_day_this_month = date('Y-m-01');
        $last_day_this_month  = date('Y-m-t');

        $this_month_deafeted =  IPTVCustomerInvoce::whereBetween('duedate_at', [$first_day_this_month, $last_day_this_month ])
        ->where('payment_at','=',null)
        ->where('canceled_at','=',null)
        ->count();

        $before_months = IPTVCustomerInvoce::where('duedate_at', '<', $first_day_this_month)
        ->where('payment_at','=',null)
        ->where('canceled_at','=',null)
        ->count();

        if($this_month_deafeted || $before_months){
            return true;
        }

        return false;
    }

}
