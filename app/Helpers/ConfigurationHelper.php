<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
class ConfigurationHelper
{
    public static $config = [];
    public static function load_config()
    {

        $results = DB::table(TABLE_SYSTEM_CONFIGURATIONS)->where('status', SYSTEM_STATUS_ACTIVE)->get();
        // // $results = SystemConfiguration::where('status', 'Active')
        // //                                 ->get();
        // print_r($results);
        foreach ($results as $result) {
            self::$config[$result->purpose] = $result->config_value;
        }

    }
    public static function get_otp_expire_time()
    {
        return isset(self::$config[SYSTEM_CONFIGURATIONS_OTP_EXPIRE]) ? self::$config[SYSTEM_CONFIGURATIONS_OTP_EXPIRE] : 10;
    }
    public static function get_company_user_invitation_expire_time()
    {
        return isset(self::$config[SYSTEM_CONFIGURATIONS_COMPANY_USER_INVITATION_EXPIRE]) ? self::$config[SYSTEM_CONFIGURATIONS_COMPANY_USER_INVITATION_EXPIRE] : 10;
    }
}
