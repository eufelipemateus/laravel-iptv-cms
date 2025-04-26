<?php

namespace FelipeMateus\IPTVCustomers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPTVCustomerInvoce extends Model
{
    use HasFactory;

    protected $table = "iptv_customer_invoces";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'iptv_customer_id', 'duedate_at', 'payment_at','canceled_at'
    ];

     /**
     * Get is payed
     *
     * @param  string  $value
     * @return boolean
     */
    public function getIsPayedAttribute($value)
    {
        if($this->payment_at){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get is canceled
     *
     * @param  string  $value
     * @return boolean
     */
    public function getIsCanceledAttribute($value)
    {
        if($this->canceled_at){
            return true;
        }else{
            return false;
        }
    }

    /**
     * customer do invoce
     *
     *  @return realation
     *
    */
    public function customer(){
        return $this->belongsTo('FelipeMateus\IPTVCustomers\Models\IPTVCustomer', 'iptv_customer_id');
    }

}
