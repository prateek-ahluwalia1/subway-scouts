<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->insert([
            'name' => 'Customer 1',
            'email' => 'customer1@customer.com',
            'password' => Hash::make(123456),
            'phone' => '1234-123-321',
            'address' => 'DHA Lahore',
            'city' => 'Lahore',
            'state' => 'panjab',
            'postal_code' => '63000',
        ]);
        DB::table('customers')->insert([
            'name' => 'Customer 2',
            'email' => 'customer2@customer.com',
            'password' => Hash::make(123456),
            'phone' => '1234-123-321',
            'address' => 'Libray Chock BWP',
            'city' => 'BWP',
            'state' => 'panjab',
            'postal_code' => '63000',
        ]);
        DB::table('customers')->insert([
            'name' => 'Customer 3',
            'email' => 'customer3@customer.com',
            'password' => Hash::make(123456),
            'phone' => '1234-123-321',
            'address' => 'DHA Karachi',
            'city' => 'Karachi',
            'state' => 'sind',
            'postal_code' => '66000',
        ]);
    }
}
