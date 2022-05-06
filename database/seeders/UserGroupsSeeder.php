<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserGroupsSeeder extends Seeder
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
                'name' => 'Super Admin',
                'ordering' => '1',
                'action_0' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_1' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_2' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_3' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Admin',
                'ordering' => '2',
                'action_0' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_1' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_2' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'action_3' => ',2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Guest',
                'ordering' => '3',
                'action_0' => ',',
                'action_1' => ',',
                'action_2' => ',',
                'action_3' => ',',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Registered',
                'ordering' => '4',
                'action_0' => ',12,',
                'action_1' => ',',
                'action_2' => ',',
                'action_3' => ',',
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
