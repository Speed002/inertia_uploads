<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request){
        $files = $request->user()->files()->latest()->get();
        return inertia()->render('Dashboard', [
            'files' => $files
        ]);
    }
}
