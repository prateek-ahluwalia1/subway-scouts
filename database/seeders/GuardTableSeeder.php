<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('guards')->insert([
            'first_name' => '247',
            'middle_name' => 'Staffing',
            'last_name' => 'Solutions',
            'email' => '247@gmail.com',
            'phone' => '+923018879242',
            'address' => 'DHA Lahore',
            'state' => 'Victoria'
        ]);
        
        DB::table('guards')->insert([
            'first_name' => '248',
            'middle_name' => 'Staffing',
            'last_name' => 'Solutions',
            'email' => '248@gmail.com',
            'phone' => '123-123-1234',
            'address' => 'DHA Lahore',
            'state' => 'Victoria'
        ]);
    }
}
