<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class BeachMapController extends Controller
{
    public function index(): View
    {
        return view('citizen.beaches.index');
    }
}
