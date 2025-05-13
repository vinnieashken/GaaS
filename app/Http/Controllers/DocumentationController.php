<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Generator;

class DocumentationController extends Controller
{
    public $versions;
    public function __construct()
    {
        $this->versions = ['v1'];
    }
    public function index()
    {
        $reversed = array_reverse($this->versions);
        $version = $reversed[0];

        return redirect()->route('docs.show',['version' => $version]);
    }

    public function documentation(Request $request, $version)
    {
        $reversed = array_reverse($this->versions);
        if(!in_array($version, $reversed)){
            $latest_version = $reversed[0];
            return redirect()->route('docs.show',['version' => $latest_version]);
        }
        $versions = $reversed;
        return view('docs.swagger', compact('version','versions'));
    }

    public function swagger(Request $request,$version)
    {
        $openapi = Generator::scan([app_path('Http/Controllers/Api/'.strtoupper($version))]);
        return response()->json(json_decode($openapi->toJson()));
    }

}
