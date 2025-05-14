<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create(
            [
            'name' => 'Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'designation' => User::INTERNAL,
            'status' => 'active',
        ]);

        $user->parent_id = $user->id;
        $user->save();

        $this->call(GatewaySeeder::class);
        $this->call(ProfileSeeder::class);

    }
}
