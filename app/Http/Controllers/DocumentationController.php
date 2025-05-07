<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Generator;

class DocumentationController extends Controller
{
    public function index()
    {
        return view('docs.swagger');
    }

    public function swagger(Request $request)
    {
        $openapi = Generator::scan([app_path('Http/Controllers')]);
        return response()->json(json_decode($openapi->toJson()));
    }
}
