<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team_Member extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'team_member';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id',
        'user_id',
    ];
}
