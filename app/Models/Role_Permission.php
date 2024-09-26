<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_Permission extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     */
    protected $table = 'permission_to_roles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_id',
        'permission_id',
        'access',
        'view_only',
    ];
}
