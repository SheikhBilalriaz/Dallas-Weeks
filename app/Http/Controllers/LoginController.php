<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
  function login(){
    $data= [

        'title'=>'Login Page'

    ];
    
    return view('Login',$data);

  }
}
