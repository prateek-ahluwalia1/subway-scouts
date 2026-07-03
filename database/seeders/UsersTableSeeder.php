<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'Prateek',
            'email' => 'prateek@gmail',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'Amna Rana',
            'email' => 'amna@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'usman',
            'email' => 'usman@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'naveed',
            'email' => 'naveed@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'admin',
            'status' => 'active',
            'is_super_admin' => 0,
            'phone' => '+923018879242',
        ]);

        DB::table('users')->insert([
            'name' => 'wajahat Naqvi',
            'email' => 'wajahat@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'Hira',
            'email' => 'hira@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'Faizan Bashir',
            'email' => 'faizan@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'Mashal',
            'email' => 'mashal@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);

        DB::table('users')->insert([
            'name' => 'krishna',
            'email' => 'krishna@gmail.com',
            'password' => Hash::make(123456),
            'userType' => 'super-admin',
            'status' => 'active',
            'is_super_admin' => 1,
            'phone' => '123-123-124',
        ]);
    }
}
