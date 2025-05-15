<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function index()
    {
        $filters = [];

        $options =[
            'model' => Currency::class,
            'title' => 'Currencies',
            'relations' => [],
            'relationsCount' => [],
            'relationsKeys' => [],
            'relationSearch' => [],
            'columns' => [
                '#' => 'id',
                'Name' => 'name',
                'Code' => 'code',
                'Country' => 'country',
                'Status' => 'status',
                'Date Created' => 'created_at',
            ],
            'search_columns' => [
                'id' => 'numeric',
                'name' => 'string',
                'code' => 'string',
            ],
            'order_columns' => ['created_at'],
            'selectors' => [
                'status' => ['active', 'inactive'],
            ],
            'filters' => $filters,
            'actions' => [
                'edit' => [
                    'method' => 'GET',
                    'route' => 'currencies.edit',
                    'type'=>'link',
                    'icon' => 'fa-regular fa-pen-to-square',
                    'permission'=> null,
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'route' => 'currencies.destroy',
                    'type'=>'confirm',
                    'icon' => 'fa-solid fa-trash text-danger',
                    'permission'=> null,
                ],
            ],
        ];

        $data['options'] = $options;

        return view('pages.currencies.index',compact('data'));
    }

    public function create()
    {
        $data = [
            'statuses' => ['inactive', 'active'],
        ];
        return view('pages.currencies.create',compact('data'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|alpha|size:3',
            'country' => 'required|alpha|size:2',
            'status' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        Currency::create( $request->only(['name','code','country','status']) );

        toastr()->success('Currency Created successfully');
        return redirect()->route('currencies');
    }

    public function edit(Currency $currency)
    {
        $data['currency'] = $currency;
        $data['statuses'] = ['inactive', 'active'];

        return view('pages.currencies.edit',compact('data'));
    }

    public function update(Request $request, Currency $currency)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|alpha|size:3',
            'country' => 'required|alpha|size:2',
            'status' => 'required',
        ]);

        if($validator->fails())
        {
            toastr()->error('Validation Error');
            return  redirect()->back()->withErrors($validator);
        }

        $currency->update( $request->only(['name','code','country','status']) );
        toastr()->success('Currency Updated successfully');
        return redirect()->route('currencies');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        toastr()->success('Currency deleted successfully');
        return redirect()->back();
    }
}
