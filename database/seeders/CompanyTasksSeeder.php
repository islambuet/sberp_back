<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_COMPANY_TASKS)->insert([
            [
                'name' => 'Setup',
                'type' => 'MODULE',
                'parent' => 0,
                'url' => '',
                'ordering' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],

            [
                'name' => 'Branches',
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'setup/branches',
                'ordering' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'User Groups',
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'setup/company-user-groups',
                'ordering' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Invite Users',
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'invite-company-users',
                'ordering' => 3,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Manage Users',
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'company-users',
                'ordering' => 4,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Schedule Management',
                'type' => 'MODULE',
                'parent' => 0,
                'url' => '',
                'ordering' => 2,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Manage Schedules',
                'type' => 'TASK',
                'parent' => 6,
                'url' => 'schedules',
                'ordering' => 4,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Invite users to Schedule',
                'type' => 'TASK',
                'parent' => 6,
                'url' => 'schedule/invite-users',
                'ordering' => 4,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Manage users Request to join Schedule',
                'type' => 'TASK',
                'parent' => 6,
                'url' => 'schedule/request-users',
                'ordering' => 4,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ],

        ]);
    }
}
