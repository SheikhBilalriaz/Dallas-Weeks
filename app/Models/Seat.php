<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'seat';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'creator_id',
        'team_id',
        'company_info_id',
        'seat_info_id',
        'subscription_id',
        'customer_id',
        'is_active',
        'is_connected',
        'slug',
    ];
}
