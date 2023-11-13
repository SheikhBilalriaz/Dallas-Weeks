<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaindashboardController extends Controller
{
   function maindasboard(){
    $data=[
        'title'=>'Account Dashboard'
    ];
    return view('main-dashboard',$data);
   }
}
