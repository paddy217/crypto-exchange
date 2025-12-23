<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users with balances
        $user1 = User::create([
            'name' => 'Paddy Trader',
            'email' => 'paddy@example.com',
            'password' => Hash::make('Password@123'),
            'balance' => 50000,
        ]);

        $user2 = User::create([
            'name' => 'Sonik Trader',
            'email' => 'sonik@example.com',
            'password' => Hash::make('Password@123'),
            'balance' => 25000,
        ]);

        // Give users some crypto assets
        Asset::create([
            'user_id' => $user1->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Asset::create([
            'user_id' => $user1->id,
            'symbol' => 'ETH',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Asset::create([
            'user_id' => $user2->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Asset::create([
            'user_id' => $user2->id,
            'symbol' => 'ETH',
            'amount' => 5,
            'locked_amount' => 0,
        ]);
    }
}
