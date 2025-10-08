<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HolaController extends Controller
{
    public function saludar()
    {
        $datos = [
            "nombre" => "Carlos",
            "edad" => 30,
        ];
        return view('hola-mundo', compact('datos'));
    }
}
