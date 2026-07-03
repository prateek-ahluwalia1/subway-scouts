<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sites')->insert([
            'booking_id' => substr(uniqid(), 0, 4).'-'.substr(uniqid(), 5, 4),
            'customer_id' => 1,
            'site_type' => 'direct',
            'site_name' => 'site1',
            'state' => 'Victoria',
            'site_description' => 'description of site 1',
            'start' => dbFormate('02-08-2023'),
            'end' => dbFormate('02-09-2023'),
        ]); 

        DB::table('sites')->insert([
            'booking_id' => substr(uniqid(), 0, 4).'-'.substr(uniqid(), 5, 4),
            'customer_id' => 1,
            'site_type' => 'direct',
            'site_name' => 'site2',
            'state' => 'Victoria',
            'site_description' => 'description of site 2',
            'start' => dbFormate('02-08-2023'),
            'end' => dbFormate('02-09-2023'),
        ]);
        
        DB::table('sites')->insert([
            'booking_id' => substr(uniqid(), 0, 4).'-'.substr(uniqid(), 5, 4),
            'customer_id' => 2,
            'site_type' => 'direct',
            'site_name' => 'site3',
            'state' => 'Victoria',
            'site_description' => 'description of site 3',
            'start' => dbFormate('02-08-2023'),
            'end' => dbFormate('02-09-2023'),
        ]);
    }
}
