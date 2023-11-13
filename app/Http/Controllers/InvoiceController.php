<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    function invoice(){
        $data=[
            'title'=>'Invoices'
        ];
        return view('invoice',$data);
    }
}
