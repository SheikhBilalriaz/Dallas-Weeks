<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Global_Permission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'global_permission';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'user_id',
        'team_id',
        'access',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'access' => 'boolean',
    ];
}
