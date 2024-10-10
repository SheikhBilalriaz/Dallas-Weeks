<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat_Time extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'seat_time';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seat_id',
        'time_status',
        'time'
    ];
}
