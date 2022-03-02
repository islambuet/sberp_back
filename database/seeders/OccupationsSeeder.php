<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OccupationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_OCCUPATIONS)->insert([
            [
                'name' => 'Nurse',                                         
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],
            [
                'name' => 'Waiter',                                         
                'created_by' => 1,
                'created_at'=>Carbon::now()
            ],
            
        ]);
    }
}
