<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_USERS)->insert([
            [
                'first_name' => 'Shaiful',
                'last_name' => 'Islam',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'user_group_id' => 1,
                'mobile_no' => '01912097849',
                'email_verified_at' => Carbon::now(),
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
