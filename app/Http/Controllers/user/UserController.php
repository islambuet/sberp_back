<?php
namespace App\Http\Controllers\user;

use App\Http\Controllers\RootController;

// use App\Helpers\TaskHelper;
// use App\Helpers\TokenHelper;
// use App\Helpers\UserHelper;
// use App\Helpers\UploadHelper;

use Illuminate\Http\Request;


// use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Schema;

// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// use App\Models\User;
use App\Mail\MailSender;
use Illuminate\Support\Facades\Mail;
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
            return response()->json(['error' => '','data' =>array()],200);
        } catch (\Exception $ex) {
            //print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();
            return response()->json(['error' => 'SERVER_ERROR', 'errorMessage'=>__('response.SERVER_ERROR')]);
        }  
    }
    public function sendOtp(Request $request)
    {
        // //accepted inputs and validation rule
        // $validation_rule=array();            
        // $validation_rule['email']=['required', 'string', 'email'];
        // $validation_rule['reason']=['required','min:3','max:255','alpha_dash'];

        // $itemNew=$request->item;

        // //checking if any input there
        // if(!is_array($itemNew)){
        //     return response()->json(['error'=>'VALIDATION_FAILED','message'=>__('validation.input_not_found')]);
        // }
        // //checking if any invalid input
        // foreach($itemNew as $key=>$value){            
        //     if( !$key || (!in_array ($key,array_keys($validation_rule)))){                        
        //         return response()->json(['error'=>'VALIDATION_FAILED','message'=>__('validation.input_not_valid',['attribute'=>$key])]);
        //     }
        // }
    }
    public function login(Request $request)
    {
        //return view('emails.otp_email_verify');
        //return Mail::to('shaiful.islam@aclusterllc.com')->send(new MailSender('emails.otp_email_verify',"test subject",['otp'=>'123']));
        //return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    
    
}
