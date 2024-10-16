<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead_Action extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'lead_action';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'current_element_id',
        'next_true_element_id',
        'next_false_element_id',
        'lead_id',
        'status',
        'campaign_id',
        'ending_time',
    ];
}
