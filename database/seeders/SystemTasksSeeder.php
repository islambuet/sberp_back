<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_SYSTEM_TASKS)->insert([
            [
                'name' => 'System Settings', 
                'type' => 'MODULE',
                'parent' => 0,
                'url' => '',                
                'ordering' => 1,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],
            [
                'name' => 'Modules & Tasks', 
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'modules-tasks',                
                'ordering' => 1,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],
            [
                'name' => 'System Configuration', 
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'system-configurations',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],
            [
                'name' => 'Users Groups', 
                'type' => 'TASK',
                'parent' => 1,
                'url' => 'users-groups',                
                'ordering' => 3,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],            
            [
                'name' => 'Users', 
                'type' => 'TASK',
                'parent' => 0,
                'url' => 'users',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],  
            [
                'name' => 'setup', 
                'type' => 'MODULE',
                'parent' => 0,
                'url' => '',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],   
            [
                'name' => 'Business', 
                'type' => 'MODULE',
                'parent' => 6,
                'url' => '',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ], 
            [
                'name' => 'Company', 
                'type' => 'TASK',
                'parent' => 7,
                'url' => 'setup/business/companies',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],      
            [
                'name' => 'Branches', 
                'type' => 'TASK',
                'parent' => 7,
                'url' => 'setup/business/branches',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ], 
            [
                'name' => 'Company User Groups', 
                'type' => 'TASK',
                'parent' => 7,
                'url' => 'setup/business/compnay-user-groups',                
                'ordering' => 2,
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],                 
        ]);
    }
}
