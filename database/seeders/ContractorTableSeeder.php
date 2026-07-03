<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contractors')->insert([
            'name' => 'Contractor1',
            'email' => 'contractor1@customer.com',
            'password' => '123456',
            'phone' => '1234-123-321',
            'address' => 'DHA Lahore',
            'city' => 'Lahore',
            'state' => 'panjab',
            'postal_code' => '63000',
        ]);
        DB::table('contractors')->insert([
            'name' => 'Contractor2',
            'email' => 'contractor2@customer.com',
            'password' => '123456',
            'phone' => '1234-123-321',
            'address' => 'Libray Chock BWP',
            'city' => 'BWP',
            'state' => 'panjab',
            'postal_code' => '63000',
        ]);
        DB::table('contractors')->insert([
            'name' => 'Contractor3',
            'email' => 'contractor3@customer.com',
            'password' => '123456',
            'phone' => '1234-123-321',
            'address' => 'DHA Karachi',
            'city' => 'Karachi',
            'state' => 'sind',
            'postal_code' => '66000',
        ]);
    }
}
