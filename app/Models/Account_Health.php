<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account_Health extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'account_health';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seat_id',
        'health_slug',
        'value'
    ];
}
