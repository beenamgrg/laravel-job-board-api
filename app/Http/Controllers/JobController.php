<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        dd('no middleware');
    }
    public function store()
    {
        dd('middleware');
    }
}
