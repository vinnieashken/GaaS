<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mpesa = Gateway::create([
            'identifier' => 'COM_MPESA_200870',
            'name' => 'MPESA',
            'provider' => 'safaricom',
            'type' => 'mobile_money',
            'status' => 'active',
            'description' =>'collect payments via MPESA',
            'config' => [
                "shortcode" => "500400",
                "consumer_key" => "q4dkUSFpgARZdTR5fLH2qJl6aHoGjJUlUbuOBsgHWOTqiuqM",
                "consumer_secret" => "bH68gbhDdMzLsmgpByzOP05w92G3b7Gv9j7qfaurFwf4OQfKHTmucRJDgBtDhjtX",
                "passkey" => "9e3c394e5389eae52c6f2df3295ff76f2a58a7f88d3593dac8253f4b81264434",
                "initiator" => "",
                "initiator_password" => "",
                "base_url" => "https://api.safaricom.co.ke",
            ]
        ]);

        $dpo = Gateway::create([
            'identifier' => 'COM_DPO',
            'name' => 'DPO',
            'provider' => 'dpo',
            'type' => 'card',
            'status' => 'active',
            'description' =>'collect payments via Direct pay online',
            'config' => [
                'company_token' => 'C6DE8B3A-2C7A-4179-BA6E-EA073C5A7371',
                'service_type' => 50997,
                'api_url' => 'https://secure.3gdirectpay.com/API/v6/',
                'redirect_url' => 'https://secure.3gdirectpay.com/payv3.php?ID=',
            ]
        ]);

        $mobile_money = Currency::create([
            'name' => 'Kenyan shilling',
            'code' => 'KES',
            'symbol' => 'KSh',
            'country' => 'KE',
        ]);

        Currency::create([
            'name' => 'Ugandan shilling',
            'code' => 'UGX',
            'symbol' => 'USh',
            'country' => 'UG',
        ]);

        Currency::create([
            'name' => 'Tanzanian shilling',
            'code' => 'TZS',
            'symbol' => 'TSh',
            'country' => 'TZ',
        ]);

        Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'country' => 'US',
        ]);

        $mpesa->currencies()->attach([$mobile_money->id]);
        $dpocurrencies = Currency::all();
        $dpo->currencies()->attach($dpocurrencies->pluck('id')->toArray());
    }
}
