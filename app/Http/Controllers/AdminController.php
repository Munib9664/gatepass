<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin-dashboard', [
            'residents'  => User::with('apartment')->where('role', 'resident')->latest()->get(),
            'watchmen'   => User::where('role', 'watchman')->latest()->get(),
            'apartments' => Apartment::with('residents')->withCount(['residents', 'visitors'])->orderBy('block')->orderBy('number')->get(),
        ]);
    }
}
