<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DasboardController extends Controller
{
   function dashboard(){
    $data =[
        'title'=>'Account Dashboard'
    ];
    return view('dashboard-account',$data);
   }
}
