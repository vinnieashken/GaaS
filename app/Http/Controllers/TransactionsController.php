<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    //
    public function index()
    {
        $filters = [];

        $options =[
            'model' => Order::class,
            'title' => 'Transactions',
            'relations' => ['gateway'],
            'relationsKeys' => ['gateway_id'],
            'relationSearch' => [
            ],
            'relationsCount' => [],
            'columns' => [
                '#' => 'id',
                'Identifier' => 'identifier',
                'Gateway' => 'gateway.identifier',
                'Provider' => 'gateway.provider',
                'Customer ID' => 'customer_identifier',
                'Invoice' => 'invoice_number',
                'Currency' => 'currency',
                'Amount' => 'amount',
                'Status' => 'status',
                'Receipt' => 'receipt',
                'Provider Code' => 'provider_code',
                'Provider Response' => 'provider-initial_response',
                'Date Created' => 'created_at',
            ],
            'search_columns' => [
                'id' => 'numeric',
                'identifier' => 'string',
                'invoice_number' => 'string',
                'customer_identifier' => 'string',
            ],
            'order_columns' => ['created_at'],
            'selectors' => [
                'status' => [Order::STATUS_PENDING,Order::STATUS_PROCESSING,Order::STATUS_SUCCESS,Order::STATUS_FAILED],
            ],
            'filters' => $filters,
            'actions' => [

            ],
        ];

        $data['options'] = $options;

        return view('pages.transactions.index', compact('data'));
    }
}
