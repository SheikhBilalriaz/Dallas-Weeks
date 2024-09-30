<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoice';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'invoice_url',
        'team_id',
        'seat_id'
    ];
}
