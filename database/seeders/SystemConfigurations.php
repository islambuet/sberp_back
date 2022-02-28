<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SystemConfigurations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(TABLE_SYSTEM_CONFIGURATIONS)->insert([
            'purpose' => SYSTEM_CONFIGURATIONS_OTP_EXPIRE,
            'description' => 'Otp expires in seconds.',
            'config_value' => 30,
            'created_by' => 1,
            'created_at'=>Carbon::now()
        ]);
    }
}
