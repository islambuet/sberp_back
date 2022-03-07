<?php
namespace App\Http\Controllers\user;

use App\Http\Controllers\RootController;

// use App\Helpers\TaskHelper;
// use App\Helpers\TokenHelper;
// use App\Helpers\UserHelper;
// use App\Helpers\UploadHelper;
use App\Helpers\OtpHelper;
use App\Helpers\ConfigurationHelper;

use Illuminate\Http\Request;


// use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Validation\Rule;

// use App\Models\User;
use App\Mail\MailSender;

use Carbon\Carbon;

class UserController extends RootController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    public function registration(Request $request)
    {
        //accepted inputs and validation rule
        $validation_rule=array();    
        $validation_rule['first_name']=['required', 'string','min:5','max:255'];
        $validation_rule['last_name']=['required', 'string','min:5','max:255'];
        $validation_rule['email']=['required', 'string', 'email', 'max:255', 'unique:'.TABLE_USERS];
        $validation_rule['password']=['required','min:3','max:255','alpha_dash'];

        $itemNew=$request->item;
        $this->validateInputKeys($itemNew,array_keys($validation_rule));
        $this->validateInputValues($itemNew,$validation_rule);

        DB::beginTransaction();
        try{
            $itemNew['password']=Hash::make($itemNew['password']);
            $itemNew['created_by']=$this->user['id'];
            $itemNew['created_at']=Carbon::now();            
            DB::table(TABLE_USERS)->insertGetId($itemNew);
            DB::commit();            
            return response()->json(['error' => '','messages'=>__('messages.registration_success'),'data' =>array()],200);
        } catch (\Exception $ex) {
            //print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();
            return response()->json(['error' => 'SERVER_ERROR', 'messages'=>__('messages.SERVER_ERROR')]);
        }  
    }
    public function sendOtp(Request $request)
    {
        
        //accepted inputs and validation rule
        $validation_rule=array();            
        $validation_rule['email']=['required', 'string', 'email'];
        $validation_rule['reason']=['required',Rule::in([0, 1,2])]; 
        $itemNew=$request->item;
        $this->validateInputKeys($itemNew,array_keys($validation_rule));
        $this->validateInputValues($itemNew,$validation_rule);

        $user = DB::table(TABLE_USERS)->select('*')->where('email',$itemNew['email'])->first();            
        if(!$user){
            return response()->json(['error'=>'EMAIL_NOT_EXISTS', 'messages'=>__('messages.email_not_exits')]);
        } 
        $expires=ConfigurationHelper::get_otp_expire_time();
        $otpInfo=OtpHelper::setOtp($user->email,$user->id,$itemNew['reason'],$expires);   
        try{
            if($itemNew['reason']==1){//reset password
                //return view('emails.otp_reset_password',['data'=>['otp'=>$otpInfo['otp']]]);
                Mail::to($user->email)->send(new MailSender('emails.otp_reset_password',__('Your Reset Password Request'),['name'=>$user->first_name.' '.$user->last_name,'otp'=>$otpInfo['otp'],'expires'=>$expires]));
            }  
            else if($itemNew['reason']==2){//change password
                Mail::to($user->email)->send(new MailSender('emails.otp_change_password',__('Your Change Password Request'),['name'=>$user->first_name.' '.$user->last_name,'otp'=>$otpInfo['otp'],'expires'=>$expires]));
                
            }  
            else{//email verification            
                Mail::to($user->email)->send(new MailSender('emails.otp_email_verify',__('Verify Your Email'),['name'=>$user->first_name.' '.$user->last_name,'otp'=>$otpInfo['otp'],'expires'=>$expires]));
                
            }  

            
            return response()->json(['error' => '','messages'=>__('Otp Sent'),'data' =>array()],200);
        } catch (\Exception $ex) {            
            return response()->json(['error' => 'SERVER_ERROR', 'messages'=>__('messages.SERVER_ERROR')]);
        }  
                
        //return view('emails.otp_email_verify',['otp'=>$otpInfo['otp']]);
        
        //return Mail::to('shaiful.islam@aclusterllc.com')->send(new MailSender('emails.otp_email_verify',"test subject",['otp'=>'123']));
        //return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    public function login(Request $request)
    {
        //accepted inputs and validation rule
        $validation_rule=array();
        $validation_rule['email']=['required', 'string', 'email', 'max:255'];
        $validation_rule['password']=['required','min:3','max:255','alpha_dash'];

        $itemNew=$request->item;
        $this->validateInputKeys($itemNew,array_keys($validation_rule));
        $this->validateInputValues($itemNew,$validation_rule);
        $userFound = DB::table(TABLE_USERS)->select('email','password','email_verified_at','status')->where('email',$itemNew['email'])->first();            
        if($userFound){
            if($userFound->status = SYSTEM_STATUS_ACTIVE){
                if(is_null($userFound->email_verified_at)){
                    return response()->json(['error'=>'EMAIL_NOT_VERIFIED', 'messages'=>__('messages.email_not_verified')]);
                }
                else{
                    if(Hash::check($itemNew['password'], $userFound->password)){
                        if(Auth::attempt(['email'=>$itemNew['email'],'password'=>$itemNew['password']]))
                        {
                            $user = Auth::user();
                            $user['authToken'] = Auth::user()->createToken('ip:'.$request->server('REMOTE_ADDR').';User agent:'.$request->server('HTTP_USER_AGENT'))->plainTextToken;                              
                            $response['data']=['authToken'=>$user['authToken']];
                            return response()->json($response, 200);
                        }else
                        {
                            $response['error'] = 'INVALID_CREDENTIALS';
                            $response['errorMessage'] = __('user.INVALID_CREDENTIALS');
                            return response()->json($response, 401);
                        }

                    }
                    else{
                        return response()->json(['error'=>'INVALID_CREDENTIALS', 'messages'=>__('messages.invalid_credentials')]);
                    }
                }

            }
            else{
                return response()->json(['error'=>'ITEM_NOT_FOUND', 'messages'=>__('messages.user_invalid')]);
            }    
        }
        else{
            return response()->json(['error'=>'EMAIL_NOT_EXISTS', 'messages'=>__('messages.email_not_exits')]);
        }
        


    }
    public function ChangePassword(Request $request)
    {
        echo "hi";
    }
    
    
}
