<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_categories')->insert([
            'document_category' => 'student_visa',
            'document_type' => json_encode(['passport' => 'Passport', 'visa' => 'Visa', 'vaccination' => 'Vaccination', 'security_license' => 'Security License', 'driver_license_front' => 'Driver License Front', 'driver_license_back' => 'Driver License Back' ]),
        ]);

        DB::table('document_categories')->insert([
            'document_category' => 'bridging_visa',
            'document_type' => json_encode(['passport' => 'Passport', 'visa' => 'Visa', 'vaccination' => 'Vaccination', 'security_license' => 'Security License', 'driver_license_front' => 'Driver License Front', 'driver_license_back' => 'Driver License Back' ]),
        ]);

        DB::table('document_categories')->insert([
            'document_category' => 'citizen',
            'document_type' => json_encode(['vaccination' => 'Vaccination', 'citizen_ship' => 'Citizen Ship', 'medicare' => 'Medicare', 'birth_certificate' => 'Birth Certificate', 'driver_license_front'=> 'Driver License Front','driver_license_back'=> 'Driver License Back','security_license' => 'Security License' ]),
        ]);

        DB::table('document_categories')->insert([
            'document_category' => 'permanent_resident',
            'document_type' => json_encode(['passport' => 'Passport', 'visa' => 'Visa', 'vaccination' => 'Vaccination', 'security_license' => 'Security License', 'driver_license_front' => 'Driver License Front', 'driver_license_back' => 'Driver License Back', 'medicare' => 'Medicare']),
        ]);

        DB::table('document_categories')->insert([
            'document_category' => 'visa_subclass_485',
            'document_type' => json_encode(['passport' => 'Passport', 'visa' => 'Visa', 'vaccination' => 'Vaccination', 'security_license' => 'Security License', 'driver_license_front' => 'Driver License Front', 'driver_license_back' => 'Driver License Back' ]),
        ]);
        DB::table('document_categories')->insert([
            'document_category' => 'other',
            'document_type' => json_encode(['passport' => 'Passport', 'visa' => 'Visa', 'vaccination' => 'Vaccination', 'security_license' => 'Security License', 'driver_license_front' => 'Driver License Front', 'driver_license_back' => 'Driver License Back' ]),
        ]);
    }
}
