<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $table = 'iptv_channel_groups';

    /**
     * Get the channels for the group.
     */
    public function channels()
    {
        return $this->hasMany('App\Models\Channel');
    }

    public function plan()
    {
        return $this->belongsTo(CustomerPlan::class, 'iptv_plan_id');
    }
}
