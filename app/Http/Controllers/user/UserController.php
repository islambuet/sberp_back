<?php
namespace App\Http\Controllers\user;

use App\Http\Controllers\RootController;

// use App\Helpers\TaskHelper;
// use App\Helpers\TokenHelper;
// use App\Helpers\UserHelper;
// use App\Helpers\UploadHelper;

use Illuminate\Http\Request;


// use Illuminate\Support\Facades\App;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;

// use App\Models\User;

// use Carbon\Carbon;

class UserController extends RootController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    public function registration(Request $request)
    {
        return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    public function login(Request $request)
    {
        return response()->json(['error'=>'VALIDATION_FAILED','errorMessage'=>__('validation.already_exists',['attribute'=>'username'])], 416);
    }
    
    
}
