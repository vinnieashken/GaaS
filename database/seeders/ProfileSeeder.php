<?php

namespace Database\Seeders;

use App\Models\Gateway;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1);

        $profile = Profile::create([
            'name' => 'app',
            'user_id' => $user->id,
        ]);

        $gateways = Gateway::all();
        $profile->gateways()->attach($gateways->pluck('id')->toArray());
    }
}
