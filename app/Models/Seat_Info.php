<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat_Info extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'seat_info';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'phone_number',
        'summary',
    ];
}
