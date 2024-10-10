<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat_Timezone extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'seat_timezone';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seat_id',
        'timezone'
    ];
}
