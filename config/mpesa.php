<?php

return [
    'auth_token_url'           =>   '/oauth/v1/generate?grant_type=client_credentials',
    'stk_checkout_url'         =>   '/mpesa/stkpush/v1/processrequest',
    'stk_query_url'            =>   '/mpesa/stkpushquery/v1/query',
    'reversal_url'             =>   '/mpesa/reversal/v1/request',
    'balance_url'              =>   '/mpesa/accountbalance/v1/query',
    'c2b_register_url'         =>   '/mpesa/c2b/v2/registerurl',
    'transtat_url'             =>   '/mpesa/transactionstatus/v1/query',
    'b2b_url'                  =>   '/mpesa/b2b/v1/paymentrequest',
    'b2c_url'                  =>   '/mpesa/b2c/v1/paymentrequest',
    'billMOptinLink'           =>   '/v1/billmanager-invoice/optin',
    'billMChangeOptinLink'     =>   '/v1/billmanager-invoice/change-optin-details',
    'billMSingleInvoice'       =>   '/v1/billmanager-invoice/single-invoicing',
    'billMBulkInvoice'         =>   '/v1/billmanager-invoice/bulk-invoicing',
    'billMCancelSingleIn'      =>   '/v1/billmanager-invoice/cancel-single-invoice',
    'qrcode'                   =>   '/mpesa/qrcode/v1/generate'
];
