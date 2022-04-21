<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_USER_GROUPS)->insert([
            [
                'name' => 'Assistant',
                'ordering' => '5',
                'action_0' => ',5,8,9,10,11,',
                'action_1' => ',8,9,10,11,',
                'action_2' => ',8,9,10,11,',
                'action_3' => ',',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
        //users
        DB::table(TABLE_USERS)->insert([
            [
                'first_name' => 'Shaiful',
                'last_name' => 'Islam',
                'email' => 'shaiful.islam@aclusterllc.com',
                'password' => Hash::make('123456'),
                'user_group_id' => 4,
                'mobile_no' => '01912097849',
                'email_verified_at' => Carbon::now(),
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Bari',
                'last_name' => 'Hossain',
                'email' => 'bari.hossain@aclusterllc.com',
                'password' => Hash::make('123456'),
                'user_group_id' => 4,
                'mobile_no' => '01912097849',
                'email_verified_at' => Carbon::now(),
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@aclusterllc.com',
                'password' => Hash::make('123456'),
                'user_group_id' => 5,
                'mobile_no' => '01912097849',
                'email_verified_at' => Carbon::now(),
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
        //companies
        DB::table(TABLE_COMPANIES)->insert([
            [
                'name' => 'Barber Shop',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'ZomZom Foods',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Food Resturant',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
        DB::table(TABLE_COMPANY_BRANCHES)->insert([
            [
                'name' => 'Main',
                'company_id' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Main',
                'company_id' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Main',
                'company_id' => 3,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Dhaka',
                'company_id' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
        DB::table(TABLE_COMPANY_USER_GROUPS)->insert([
            [
                'name' => 'Owner',
                'company_id' => 1,
                'action_0' => ',2,3,4,5,',
                'action_1' => ',2,3,4,5,',
                'action_2' => ',2,3,4,5,',
                'action_3' => ',2,3,4,5,',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Owner',
                'company_id' => 2,
                'action_0' => ',2,3,4,5,',
                'action_1' => ',2,3,4,5,',
                'action_2' => ',2,3,4,5,',
                'action_3' => ',2,3,4,5,',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Owner',
                'company_id' => 3,
                'action_0' => ',2,3,4,5,',
                'action_1' => ',2,3,4,5,',
                'action_2' => ',2,3,4,5,',
                'action_3' => ',2,3,4,5,',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Manager',
                'action_0' => ',4,5,',
                'action_1' => ',4,5,',
                'action_2' => ',4,5,',
                'action_3' => ',',
                'company_id' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);

        DB::table(TABLE_COMPANY_USERS)->insert([
            [
                'company_id' => 1,
                'user_id' => 3,
                'company_user_group_id' => 1,
                'company_branch_ids' => ',1,',
                'updated_by' => 1,
                'updated_at' => Carbon::now(),
            ],
            [
                'company_id' => 2,
                'user_id' => 3,
                'company_user_group_id' => 4,
                'company_branch_ids' => ',2,',
                'updated_by' => 1,
                'updated_at' => Carbon::now(),
            ],
            [
                'company_id' => 2,
                'user_id' => 2,
                'company_user_group_id' => 2,
                'company_branch_ids' => ',2,4,',
                'updated_by' => 1,
                'updated_at' => Carbon::now(),
            ],
            // [
            //     'name' => 'Main',
            //     'company_id' => 2,
            //     'created_by' => 1,
            //     'created_at' => Carbon::now(),
            // ],
            // [
            //     'name' => 'Main',
            //     'company_id' => 3,
            //     'created_by' => 1,
            //     'created_at' => Carbon::now(),
            // ],
            // [
            //     'name' => 'Dhaka',
            //     'company_id' => 2,
            //     'created_by' => 1,
            //     'created_at' => Carbon::now(),
            // ],
        ]);

    }
}
