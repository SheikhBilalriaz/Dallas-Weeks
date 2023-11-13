<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlacklistController extends Controller
{
function blacklist(){
    $data=[
        'title'=>'Blacklist'
    ];
    return view('blacklist',$data);
}
}
