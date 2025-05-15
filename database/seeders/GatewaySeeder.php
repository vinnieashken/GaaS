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
            'provider' => 'mpesa',
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
                "api_url" => "https://api.safaricom.co.ke",
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

        $paypal = Gateway::create([
            'identifier' => 'COM_PAYPAL',
            'name' => 'PAYPAL',
            'provider' => 'paypal',
            'type' => 'card',
            'status' => 'active',
            'description' =>'collect payments via paypal online',
            'config' => [
                'client_id' => 'AaoT21awRHtUfjfZ0CDVEBUc49VNHXeHcOCw9arqOzeDPN8bHGzuRDBCl1kpWpy0m0hHwY4d8XpediVt',
                'client_secret' => 'ECUYz2CBjBupWsAKZ-xNTkT-xce6gW2Q205_GiYRuQkuel6csGkIlSk5eTKd7FcBuDMtt7zN8-ItV7TD',
                'api_url' => 'https://sandbox.paypal.com',
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

        $usd = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'country' => 'US',
        ]);

        $euro = Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => 'EUR',
            'country' => 'EU',
        ]);

        $mpesa->currencies()->attach([$mobile_money->id]);

        $allcurrencies = Currency::all();

        $dpo->currencies()->attach($allcurrencies->pluck('id')->toArray());

        $paypal->currencies()->attach([$usd->id,$euro->id]);
    }
}
