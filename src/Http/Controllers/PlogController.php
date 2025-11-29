<?php

namespace Laikmosh\Plog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class PlogController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('viewPlog')) {
            abort(403, 'Unauthorized - Your email is not authorized to view logs');
        }

        return view('plog::layout');
    }
}