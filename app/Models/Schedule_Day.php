<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule_Day extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'schedule_day';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'schedule_id',
        'start_time',
        'end_time',
        'day',
        'is_active',
    ];
}
