<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
   function setting(){
    $data=[
        'title'=>'Setting'
    ];
    return view('setting',$data);
   }
}
