<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
 function team(){
    $data=[
        'title'=>'Team Dashboard'
    ];
    return view('team',$data);
 }
}
