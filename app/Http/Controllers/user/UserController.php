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
        
        // //accepted inputs and validation rule
        $validation_rule=array();            
        $validation_rule['email']=['required', 'string', 'email'];
        $validation_rule['reason']=['required',Rule::in([0, 1])]; 
        $itemNew=$request->item;
        $this->validateInputKeys($itemNew,array_keys($validation_rule));
        $this->validateInputValues($itemNew,$validation_rule);

        $user = DB::table(TABLE_USERS)->select('*')->where('email',$itemNew['email'])->first();            
        if(!$user){
            return response()->json(['error'=>'EMAIL_NOT_EXISTS', 'messages'=>__('messages.email_not_exits')]);
        }
    }
    public function login(Request $request)
    {
        //return view('emails.otp_email_verify');
        //return Mail::to('shaiful.islam@aclusterllc.com')->send(new MailSender('emails.otp_email_verify',"test subject",['otp'=>'123']));
        //return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    
    
}
