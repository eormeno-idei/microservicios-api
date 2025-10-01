<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HolaController extends Controller
{
    public function saludar()
    {
        return view("hola-mundo");
    }
}
