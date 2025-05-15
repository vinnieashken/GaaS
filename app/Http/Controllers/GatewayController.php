<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NunoMaduro\Collision\Provider;

class GatewayController extends Controller
{
    //
    public function index()
    {
        $filters = [];

        $options =[
            'model' => Gateway::class,
            'title' => 'Gateways',
            'relations' => [],
            'relationsCount' => [],
            'relationsKeys' => [],
            'relationSearch' => [],
            'columns' => [
                '#' => 'id',
                'Identifier' => 'identifier',
                'Name' => 'name',
                'Provider' => 'provider',
                'Type' => 'type',
                'Description' => 'description',
                'Image_url' => 'image_url',
                'Status' => 'status',
                'Date Created' => 'created_at',
            ],
            'search_columns' => [
                'id' => 'numeric',
                'name' => 'string',
                'identifier' => 'string',
                'provider' => 'string',
            ],
            'order_columns' => ['created_at'],
            'selectors' => [
                'status' => ['active', 'inactive'],
            ],
            'filters' => $filters,
            'actions' => [
                'edit' => [
                    'method' => 'GET',
                    'route' => 'gateways.edit',
                    'type'=>'link',
                    'icon' => 'fa-regular fa-pen-to-square',
                    'permission'=> null,
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'route' => 'gateways.destroy',
                    'type'=>'confirm',
                    'icon' => 'fa-solid fa-trash text-danger',
                    'permission'=> null,
                ],
            ],
        ];

        $data['options'] = $options;

        return view('pages.gateways.index', compact('data'));
    }

    public function create()
    {
        $data = [
            'statuses' => ['active', 'inactive'],
            'providers' => ['mpesa','paypal','dpo'],
            'types' => ['mobile_money','card'],
        ];

        $data['currencies'] = Currency::where('status','active')->get();

        return view('pages.gateways.create', compact('data'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'name' => 'required',
            'provider' => 'required',
            'type' => 'required',
            'description' => 'required',
            'image_url' => 'sometimes',
            'status' => 'required',
            'config' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }
        $request->merge(['config' => json_decode($request->config,true)]);
        $gateway = Gateway::create($request->only(['identifier', 'name', 'provider', 'type', 'description', 'image_url', 'status','config']));
        $gateway->currencies()->attach($request->currencies);
        toastr()->success('Gateway Created successfully');
        return redirect()->route('gateways');
    }

    public function edit(Gateway $gateway)
    {
        $gateway->load('currencies');

        $data = [
            'statuses' => ['active', 'inactive'],
            'providers' => ['mpesa','paypal','dpo'],
            'types' => ['mobile_money','card'],
        ];
        $data['currencies'] = Currency::where('status','active')->get();
        $data['gateway'] = $gateway;

        return view('pages.gateways.edit', compact('data'));
    }

    public function update(Request $request, Gateway $gateway)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'name' => 'required',
            'provider' => 'required',
            'type' => 'required',
            'description' => 'required',
            'image_url' => 'sometimes',
            'status' => 'required',
            'config' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        $request->merge(['config' => json_decode($request->config,true)]);

        $gateway->update($request->only(['identifier', 'name', 'provider', 'type', 'description', 'image_url', 'status','config']));
        $gateway->currencies()->sync($request->currencies);
        toastr()->success('Gateway updated successfully');
        return redirect()->route('gateways');
    }

    public function destroy(Gateway $gateway)
    {
        $gateway->delete();
        toastr()->success('Gateway deleted successfully!');
        return redirect()->route('gateways');
    }
}
