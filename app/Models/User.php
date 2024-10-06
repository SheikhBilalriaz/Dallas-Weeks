<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Get the teams created by user.
     */
    public function createdTeams()
    {
        return $this->hasMany(Team::class, 'creator_id');
    }

    /**
     * Get the global blacklists created by user.
     */
    public function createdGlobalBlacklists()
    {
        return $this->hasMany(Global_Blacklist::class, 'creator_id');
    }

    /**
     * Get the email blacklists created by user.
     */
    public function createdEmailBlacklists()
    {
        return $this->hasMany(Email_Blacklist::class, 'creator_id');
    }

    /**
     * Get the global permissions assigned to this user.
     */
    public function globalPermissions()
    {
        return $this->hasMany(Global_Permission::class, 'user_id');
    }
}
