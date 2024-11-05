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
        'team_id',
        'keyword',
        'blacklist_type',
        'comparison_type',
    ];
}
