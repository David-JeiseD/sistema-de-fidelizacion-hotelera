<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard'); // Esta será la vista principal luego del login
    }
}