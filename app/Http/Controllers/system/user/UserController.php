<?php
namespace App\Http\Controllers\system\user;

use App\Helpers\CompanyTaskHelper;
use App\Helpers\ConfigurationHelper;
use App\Helpers\OtpHelper;
use App\Helpers\TaskHelper;
use App\Helpers\TokenHelper;
// use App\Helpers\UploadHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\RootController;
use App\Mail\MailSender;

// use Illuminate\Support\Facades\App;
use App\Models\User;
// use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends RootController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    public function registration(Request $request)
    {
        //accepted inputs and validation rule
        $validation_rule = [];
        $validation_rule['first_name'] = ['required', 'string', 'min:2', 'max:255'];
        $validation_rule['last_name'] = ['required', 'string', 'min:2', 'max:255'];
        $validation_rule['email'] = ['required', 'string', 'email', 'max:255', 'unique:' . TABLE_USERS];
        $validation_rule['password'] = ['required', 'min:3', 'max:255', 'alpha_dash'];

        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }
        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        DB::beginTransaction();
        try {
            $itemNew['password'] = Hash::make($itemNew['password']);
            $itemNew['created_by'] = $this->user->id;
            $itemNew['created_at'] = Carbon::now();
            DB::table(TABLE_USERS)->insertGetId($itemNew);
            DB::commit();

            return response()->json(['error' => '', 'messages' => __('messages.registration_success'), 'data' => []], 200);
        } catch (\Exception $ex) {
            //print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();

            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }
    }
    public function sendOtp(Request $request)
    {

        //accepted inputs and validation rule
        $validation_rule = [];
        $validation_rule['email'] = ['required', 'string', 'email'];
        $validation_rule['reason'] = ['required', Rule::in([0, 1, 2])];
        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }
        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $user = DB::table(TABLE_USERS)->select('*')->where('email', $itemNew['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'EMAIL_NOT_EXISTS', 'messages' => __('messages.email_not_exits')]);
        }
        $expires = ConfigurationHelper::get_otp_expire_time();
        $otpInfo = OtpHelper::setOtp($user->email, $user->id, $itemNew['reason'], $expires);
        try {
            if ($itemNew['reason'] == 1) { //reset password
                //return view('emails.otp_reset_password',['data'=>['otp'=>$otpInfo['otp']]]);
                Mail::to($user->email)->send(new MailSender('emails.otp_reset_password', __('Your Reset Password Request'), ['name' => $user->first_name . ' ' . $user->last_name, 'otp' => $otpInfo['otp'], 'expires' => $expires]));
            } else if ($itemNew['reason'] == 2) { //change password
                Mail::to($user->email)->send(new MailSender('emails.otp_change_password', __('Your Change Password Request'), ['name' => $user->first_name . ' ' . $user->last_name, 'otp' => $otpInfo['otp'], 'expires' => $expires]));

            } else { //email verification
                Mail::to($user->email)->send(new MailSender('emails.otp_email_verify', __('Verify Your Email'), ['name' => $user->first_name . ' ' . $user->last_name, 'otp' => $otpInfo['otp'], 'expires' => $expires]));

            }

            return response()->json(['error' => '', 'messages' => __('Otp Sent'), 'data' => []], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }

        //return view('emails.otp_email_verify',['otp'=>$otpInfo['otp']]);

        //return Mail::to('shaiful.islam@aclusterllc.com')->send(new MailSender('emails.otp_email_verify',"test subject",['otp'=>'123']));
        //return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    public function login(Request $request)
    {
        //accepted inputs and validation rule
        $validation_rule = [];
        $validation_rule['email'] = ['required', 'string', 'email', 'max:255'];
        $validation_rule['password'] = ['required', 'min:3', 'max:255', 'alpha_dash'];

        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }
        $userFound = DB::table(TABLE_USERS)->select('email', 'password', 'email_verified_at', 'status')->where('email', $itemNew['email'])->first();
        if ($userFound) {
            if ($userFound->status == SYSTEM_STATUS_ACTIVE) {
                if (is_null($userFound->email_verified_at)) {
                    return response()->json(['error' => 'EMAIL_NOT_VERIFIED', 'messages' => __('messages.email_not_verified')]);
                } else {
                    if (Hash::check($itemNew['password'], $userFound->password)) {
                        if (Auth::attempt(['email' => $itemNew['email'], 'password' => $itemNew['password']])) {
                            $user = Auth::user();
                            $user->authToken = $user->createToken('ip:' . $request->server('REMOTE_ADDR') . ';User agent:' . $request->server('HTTP_USER_AGENT'))->plainTextToken;
                            $usertoReturn = $user->toArray();
                            $usertoReturn['default_menu'] = TaskHelper::getUserGroupMenu(UserHelper::getCurrentUser()->userGroupRole);
                            $usertoReturn['companies'] = UserHelper::getUserCompanies($usertoReturn['id']);
                            return response()->json(['error' => '', 'messages' => __('Logged in successfully'), 'data' => $usertoReturn], 200);
                        } else {
                            $response['error'] = 'INVALID_CREDENTIALS';
                            $response['messages'] = __('user.INVALID_CREDENTIALS');
                            return response()->json($response, 200);
                        }

                    } else {
                        return response()->json(['error' => 'INVALID_CREDENTIALS', 'messages' => __('messages.invalid_credentials')]);
                    }
                }

            } else {
                return response()->json(['error' => 'ITEM_NOT_FOUND', 'messages' => __('messages.user_invalid')]);
            }
        } else {
            return response()->json(['error' => 'EMAIL_NOT_EXISTS', 'messages' => __('messages.email_not_exits')]);
        }

    }
    //otp reason =2
    public function ChangePassword(Request $request)
    {

        $save_token = TokenHelper::getSaveToken($request->save_token, $this->user->id);
        if (isset($save_token['error']) && strlen($save_token['error']) > 0) {
            return response()->json($save_token);
        }
        $itemId = $this->user->id;
        $validation_rule = [];
        $validation_rule['otp'] = ['required'];
        $validation_rule['password_new'] = ['required', 'min:3', 'max:255', 'alpha_dash'];
        $validation_rule['password_old'] = ['required', 'min:3', 'max:255', 'alpha_dash'];

        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $otpInfo = OtpHelper::checkOtp($this->user->email, $itemNew['otp'], 2);
        if (is_array($otpInfo) && isset($otpInfo['error']) && strlen($otpInfo['error']) > 0) {
            return response()->json($otpInfo);
        }

        $result = DB::table(TABLE_USERS)->select('password')->find($itemId);
        if (!(Hash::check($itemNew['password_old'], $result->password))) {
            return response()->json(['error' => 'INVALID_CREDENTIALS', 'messages' => __('messages.invalid_credentials')]);
        }
        $itemOld = [];
        $itemOld['password'] = $result->password;

        $password_new = $itemNew['password_new'];
        $itemNew = [];
        $itemNew['password'] = Hash::make($password_new);
        DB::beginTransaction();
        try {

            $dataHistory = [];
            $dataHistory['table_name'] = TABLE_USERS;
            $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
            $dataHistory['method'] = __FUNCTION__;

            $itemNew['updated_by'] = $this->user->id;
            $itemNew['updated_at'] = Carbon::now();
            DB::table(TABLE_USERS)->where('id', $itemId)->update($itemNew);
            $dataHistory['table_id'] = $itemId;
            $dataHistory['action'] = DB_ACTION_EDIT;
            unset($itemNew['updated_by'], $itemNew['created_by'], $itemNew['created_at'], $itemNew['updated_at']);

            $dataHistory['data_old'] = json_encode($itemOld);
            $dataHistory['data_new'] = json_encode($itemNew);
            $dataHistory['created_at'] = Carbon::now();
            $dataHistory['created_by'] = $this->user->id;

            $this->dBSaveHistory($dataHistory, TABLE_SYSTEM_HISTORIES);
            TokenHelper::updateSaveToken($save_token);
            OtpHelper::updateOtp($otpInfo);

            //delete all sessions
            $this->user->tokens()->delete();
            //create new sessions
            $authToken = $this->user->createToken('ip:' . $request->server('REMOTE_ADDR') . ';User agent:' . $request->server('HTTP_USER_AGENT'))->plainTextToken;
            DB::commit();

            return response()->json(['error' => '', 'messages' => __('Password Changed'), 'data' => ['authToken' => $authToken]], 200);
        } catch (\Exception $ex) {
            print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();

            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }
    }
    public function recoverPassword(Request $request)
    {
        $validation_rule = [];
        $validation_rule['otp'] = ['required'];
        $validation_rule['email'] = ['required', 'string', 'email'];
        $validation_rule['password_new'] = ['required', 'min:3', 'max:255', 'alpha_dash'];
        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $user = User::where('email', $itemNew['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'EMAIL_NOT_EXISTS', 'messages' => __('messages.email_not_exits')]);
        }
        $itemId = $user->id;
        $otpInfo = OtpHelper::checkOtp($itemNew['email'], $itemNew['otp'], 2);
        if (is_array($otpInfo) && isset($otpInfo['error']) && strlen($otpInfo['error']) > 0) {
            return response()->json($otpInfo);
        }

        $itemOld = [];
        $itemOld['password'] = $user->password;

        $password_new = $itemNew['password_new'];
        $itemNew = [];
        $itemNew['password'] = Hash::make($password_new);
        if (is_null($user->email_verified_at)) {
            $itemOld['email_verified_at'] = $user->email_verified_at;
            $itemNew['email_verified_at'] = Carbon::now();
        }
        DB::beginTransaction();
        try {

            $dataHistory = [];
            $dataHistory['table_name'] = TABLE_USERS;
            $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
            $dataHistory['method'] = __FUNCTION__;

            $itemNew['updated_by'] = $this->user->id;
            $itemNew['updated_at'] = Carbon::now();
            DB::table(TABLE_USERS)->where('id', $itemId)->update($itemNew);
            $dataHistory['table_id'] = $itemId;
            $dataHistory['action'] = DB_ACTION_EDIT;
            unset($itemNew['updated_by'], $itemNew['created_by'], $itemNew['created_at'], $itemNew['updated_at']);

            $dataHistory['data_old'] = json_encode($itemOld);
            $dataHistory['data_new'] = json_encode($itemNew);
            $dataHistory['created_at'] = Carbon::now();
            $dataHistory['created_by'] = $this->user->id;

            $this->dBSaveHistory($dataHistory, TABLE_SYSTEM_HISTORIES);

            OtpHelper::updateOtp($otpInfo);

            //delete all sessions
            $user->tokens()->delete();

            DB::commit();

            return response()->json(['error' => '', 'messages' => __('Password Reset done'), 'data' => []], 200);
        } catch (\Exception $ex) {
            print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();

            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }
    }
    public function verifyEmail(Request $request)
    {
        $validation_rule = [];
        $validation_rule['otp'] = ['required'];
        $validation_rule['email'] = ['required', 'string', 'email'];
        $itemNew = $request->item;
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if (isset($validation['error']) && strlen($validation['error']) > 0) {
            return response()->json($validation);
        }

        $user = User::where('email', $itemNew['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'EMAIL_NOT_EXISTS', 'messages' => __('messages.email_not_exits')]);
        }
        $itemId = $user->id;
        $otpInfo = OtpHelper::checkOtp($itemNew['email'], $itemNew['otp'], 2);
        if (is_array($otpInfo) && isset($otpInfo['error']) && strlen($otpInfo['error']) > 0) {
            return response()->json($otpInfo);
        }

        $itemOld = [];
        $itemOld['email_verified_at'] = $user->email_verified_at;

        $itemNew = [];
        $itemNew['email_verified_at'] = Carbon::now();
        DB::beginTransaction();
        try {

            $dataHistory = [];
            $dataHistory['table_name'] = TABLE_USERS;
            $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
            $dataHistory['method'] = __FUNCTION__;

            $itemNew['updated_by'] = $this->user->id;
            $itemNew['updated_at'] = Carbon::now();
            DB::table(TABLE_USERS)->where('id', $itemId)->update($itemNew);
            $dataHistory['table_id'] = $itemId;
            $dataHistory['action'] = DB_ACTION_EDIT;
            unset($itemNew['updated_by'], $itemNew['created_by'], $itemNew['created_at'], $itemNew['updated_at']);

            $dataHistory['data_old'] = json_encode($itemOld);
            $dataHistory['data_new'] = json_encode($itemNew);
            $dataHistory['created_at'] = Carbon::now();
            $dataHistory['created_by'] = $this->user->id;

            $this->dBSaveHistory($dataHistory, TABLE_SYSTEM_HISTORIES);

            OtpHelper::updateOtp($otpInfo);

            DB::commit();

            return response()->json(['error' => '', 'messages' => __('Email verified'), 'data' => []], 200);
        } catch (\Exception $ex) {
            print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();

            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }
    }
    public function getDefaultMenu(Request $request)
    {
        return response()->json(['error' => '', 'data' => TaskHelper::getUserGroupMenu($this->user->userGroupRole)]);
    }

    public function getCompanies(Request $request)
    {
        return response()->json(['error' => '', 'data' => UserHelper::getUserCompanies($this->user->id)]);
    }
    public function getCompanyMenu($companyId, Request $request)
    {
        return response()->json(['error' => '', 'data' => CompanyTaskHelper::getCompanyUserGroupMenu($companyId, $this->user->companyUserGroupRole)]);

    }
}
