<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_Info extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'company_info';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
    ];
}
