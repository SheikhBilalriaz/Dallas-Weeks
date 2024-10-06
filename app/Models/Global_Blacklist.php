<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Global_Blacklist extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'global_blacklist';

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'team_id',
        'keyword',
        'blacklist_type',
        'comparison_type',
    ];

    /**
     * Get the creator of the global blacklist entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the team that owns the global blacklist entry.
     */
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
