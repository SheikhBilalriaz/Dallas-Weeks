<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assigned_Seat extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     */
    protected $table = 'assigned_seat';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'seat_id',
        'team_id',
    ];
}
