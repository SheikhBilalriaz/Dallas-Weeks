<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'lead';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'campaign_id',
        'is_active',
        'send_connections',
        'profileUrl',
        'email',
        'contact',
        'title_company',
        'address',
        'website',
        'provider_id',
        'executed_time',
    ];
}
