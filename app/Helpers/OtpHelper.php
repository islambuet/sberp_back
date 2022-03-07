<?php
    namespace App\Helpers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    class OtpHelper
    {
        
        public static function setOtp($email,$user_id,$reason,$expires)
        {
            $itemNew=array();            
            $itemNew['user_id']=$user_id;
            $itemNew['email']=$email;
            $itemNew['reason']=$reason;
            $itemNew['otp']=rand(1000,999999);            
            $itemNew['created_at']=Carbon::now();            
            $itemNew['expires_at']=$itemNew['created_at']->copy()->addSeconds($expires);                 
            $itemNew['id'] = DB::table(TABLE_USER_OTPS)->insertGetId($itemNew);
            return $itemNew;
        }
        public static function getLastOtp($email){

        }
        public static function updateOtp($id){

        }
        
    }
