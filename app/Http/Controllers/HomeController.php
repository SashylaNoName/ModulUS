<?php
namespace App\Http\Controllers;

class HomeController extends Controller
{
    /** Лендинг */
    public function index()
    {
        return view('home');
    }
}
