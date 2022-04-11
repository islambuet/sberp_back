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
        //reason not cheacking
        public static function checkOtp($email,$otp,$reason){
            $result = DB::table(TABLE_USER_OTPS)->where('email', $email)->orderBy('id','desc')->first();
            if($result)
            {  
                if($result->otp!= $otp){                    
                    return ['error'=>'OTP_MISMATCHED','messages'=>__('validation.otp_mismatched')];
                }              
                if($result->expires_at<Carbon::now()){
                    return ['error'=>'OTP_EXPIRED','messages'=>__('validation.otp_expired')];                    
                }
                if(!(is_null($result->updated_at))){
                    return ['error'=>'OTP_USED','messages'=>__('validation.otp_already_used')];
                }
            }
            else{
                return ['error'=>'OTP_INVALID','messages'=>__('validation.otp_not_found')];
            }
            return $result;

        }
        public static function updateOtp($otpInfo){
            $itemNew=array();                             
            $itemNew['updated_at']=Carbon::now();
            DB::table(TABLE_USER_OTPS)->where('id',$otpInfo->id)->update($itemNew);
        }
        
    }
