<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppsController extends Controller
{
    //
    public function index()
    {
        $filters = [];

        $options =[
            'model' => Profile::class,
            'title' => 'Apps',
            'relations' => ['user'],
            'relationsCount' => [],
            'relationsKeys' => ['user_id'],
            'relationSearch' => [
                'user' => [
                    'name' => 'string',
                    'email' => 'string',
                ]
            ],
            'columns' => [
                '#' => 'id',
                //'User id' => 'user_id',
                'Name' => 'name',
                'Client ID' => 'key',
                'Owner Email' => 'user.email',
                'Owner Name' => 'user.name',
                'description' => 'description',
                'Status' => 'status',
                'Date Created' => 'created_at',
            ],
            'search_columns' => [
                'id' => 'numeric',
                'name' => 'string',
                'key' => 'string',
            ],
            'order_columns' => ['created_at'],
            'selectors' => [
                'status' => ['active', 'inactive'],
            ],
            'filters' => $filters,
            'actions' => [
                'view' => [
                    'method' => 'GET',
                    'route' => 'apps.show',
                    'type'=>'link',
                    'icon' => 'fa-regular fa-eye',
                    'permission'=> null,
                ],
                'edit' => [
                    'method' => 'GET',
                    'route' => 'apps.edit',
                    'type'=>'link',
                    'icon' => 'fa-regular fa-pen-to-square',
                    'permission'=> null,
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'route' => 'apps.destroy',
                    'type'=>'confirm',
                    'icon' => 'fa-solid fa-trash text-danger',
                    'permission'=> null,
                ],
            ],
        ];

        $data['options'] = $options;

        return view('pages.apps.index', compact('data'));
    }

    public function create()
    {
        $data = [
            'statuses' => ['inactive', 'active'],
        ];
        return view('pages.apps.create',compact('data'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
            'description' => 'sometimes|nullable',
            'parent_email' => 'sometimes|nullable',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        if($request->has('parent_email'))
        {
            $parent = User::where('email',$request->parent_email)->first();
        }

        $data = [
            'user_id' => (isset($parent)) ? $parent->id : @auth()->user()->parent_id,
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description
        ];

        Profile::create($data);

        toastr()->success('App updated successfully');
        return redirect()->route('apps');
    }

    public function show(Profile $app)
    {
        $app->load(['user','gateways']);
        $data['app'] = $app;

        return view('pages.apps.show',compact('data'));
    }

    public function edit(Profile $app)
    {
        $app->load('user');
        $data['app'] = $app;
        $data['statuses'] = ['inactive', 'active'];

        return view('pages.apps.edit',compact('data'));
    }

    public function update(Request $request, Profile $app)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
            'description' => 'sometimes|nullable',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        if($request->has('parent_email'))
        {
            $parent = User::where('email',$request->parent_email)->first();
        }

        $data = [
            'user_id' => (isset($parent)) ? $parent->id : @auth()->user()->parent_id,
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description
        ];

        $app->update($data);

        toastr()->success('App updated successfully');
        return redirect()->route('apps');
    }

    public function destroy(Profile $app)
    {
        $app->delete();
        toastr()->success('App deleted successfully!');
        return redirect()->back();
    }
}
