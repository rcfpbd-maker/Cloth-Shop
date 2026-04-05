<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Owner
        \App\Models\User::updateOrCreate(['email' => 'owner@shop.com'], [
            'name' => 'Shop Owner',
            'phone' => '01700000001',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 1,
            'status' => 1,
        ]);

        // Manager
        \App\Models\User::updateOrCreate(['email' => 'manager@shop.com'], [
            'name' => 'Store Manager',
            'phone' => '01700000002',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 2,
            'status' => 1,
        ]);

        // Salesman
        \App\Models\User::updateOrCreate(['email' => 'salesman@shop.com'], [
            'name' => 'Sales Staff',
            'phone' => '01700000003',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 3,
            'status' => 1,
        ]);

        // Accountant
        \App\Models\User::updateOrCreate(['email' => 'accountant@shop.com'], [
            'name' => 'Accountant Staff',
            'phone' => '01700000004',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 4,
            'status' => 1,
        ]);
    }
}
