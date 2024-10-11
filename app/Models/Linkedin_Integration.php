<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linkedin_Integration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'linkedin_integration';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'seat_id',
        'account_id',
    ];
}
