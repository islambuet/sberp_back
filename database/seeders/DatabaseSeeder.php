<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([                       
            UsersGroups::class,
            // SystemTasksSeeder::class,            
            // SystemConfigurationsSeeder::class, 
            // UsersTypesSeeder::class,
            // UsersSeeder::class,            
        ]);
    }
}
