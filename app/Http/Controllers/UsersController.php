<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class UsersController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $options =[
            'model' => User::class,
            'relations' => [],
            'relationsCount' => [],
            'columns' => [
                '#' => 'id',
                'Email' => 'email',
                'Name' => 'name',
                'Status' => 'status',
                'Date Created' => 'created_at',
            ],
            'search_columns' => [
                'id' => 'numeric',
                'name' => 'string',
                'email' => 'string',
                ],
            'order_columns' => ['created_at'],
            'selectors' => [
                'status' => ['active', 'inactive'],
            ],
            'filters' => [
            ],
            'actions' => [
                'edit' => [
                    'method' => 'GET',
                    'route' => 'users.edit',
                    'type'=>'link',
                    'icon' => 'fa-regular fa-pen-to-square',
                    'permission'=> null,
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'route' => 'users.destroy',
                    'type'=>'confirm',
                    'icon' => 'fa-solid fa-trash text-danger',
                    'permission'=> null,
                ],
            ],
        ];

        $data['options'] = $options;

        return view('pages.users.index',compact('data'));
    }

    public function create()
    {
        $data['statuses'] = ['active','inactive'];
        $data['permissions'] = Permission::whereScope('internal')->get()->groupBy('group');

        return view('pages.users.create',compact('data'));
    }

    public function store(Request $request)
    {
        $user = User::where('email',$request->email)->limit(1)->first();
        $validator = Validator::make($request->all(),[
            'email' => ' required | unique:users,email',
            'name' => 'required',
            'password' => 'required | confirmed |min:6',
            'password_confirmation' => 'required',
            'status' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        try{

            if($user)
            {
                $data = [
                    'name' => $request->name,
                    'status' => $request->status,
                ];
                $user->update($data);
                $user->restore();
            }
            else{

                $user = User::create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'status' => $request->status,
                ]);
            }
            if($request->permissions)
            {
                $permissions = Permission::whereIn('id',$request->permissions)->get();
                $user->givePermissionTo($permissions);
            }

        }catch (\Exception $e)
        {
            toastr()->error('Error '.$e->getMessage());
            return redirect()->back();
        }

        toastr()->success('User added successfully');

        return redirect()->route('users');
    }

    public function edit(User $user)
    {
        $data['statuses'] = ['active','inactive'];
        $data['user'] = $user;
        $data['permissions'] = Permission::whereScope('internal')->get()->groupBy('group');

        return view('pages.users.edit',compact('data'));
    }

    public function update(Request $request,User $user)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required | unique:users,email,'.$user->id,
            'name' => 'required',
            'password' => 'required_with:password_confirmation | confirmed',
            'password_confirmation' => 'required_with:password',
            'status' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        try{
            $data = [
                'email' => $request->email,
                'name' => $request->name,
                'status' => $request->status,
                //'phone' => $request->phone
            ];
            if($request->has('password') && !is_null($request->password))
            {
                $data['password'] = Hash::make($request->password);
            }
            $user->update($data);

            if($request->permissions)
            {
                $permissions = Permission::whereIn('id',$request->permissions)->get();
                $user->syncPermissions($permissions);
            }


        }catch (\Exception $e)
        {
            toastr()->error('Error '.$e->getMessage());
            return redirect()->back();
        }

        toastr()->success('User updated successfully');
        return redirect()->route('users');
    }

    public function destroy(User $user)
    {
        $user->delete();
        toastr()->success('User deleted successfully!');
        return redirect()->back();
    }
}
