<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'team';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'creator_id',
    ];

    /**
     * Get the global blacklist associated with the team.
     */
    public function globalBlacklist()
    {
        return $this->hasMany(Global_Blacklist::class, 'team_id');
    }

    /**
     * Get the email blacklist associated with the team.
     */
    public function emailBlacklist()
    {
        return $this->hasMany(Email_Blacklist::class, 'team_id');
    }
}
