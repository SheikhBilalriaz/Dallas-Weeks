<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'creator_id',
    ];

    /**
     * Get the creator of the team.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the global blacklist associated with the team.
     */
    public function globalBlacklists()
    {
        return $this->hasMany(Global_Blacklist::class, 'team_id');
    }

    /**
     * Get the email blacklist associated with the team.
     */
    public function emailBlacklists()
    {
        return $this->hasMany(Email_Blacklist::class, 'team_id');
    }

    /**
     * Get the global permissions assigned in this team to user.
     */
    public function globalPermissions()
    {
        return $this->hasMany(Global_Permission::class);
    }
}
