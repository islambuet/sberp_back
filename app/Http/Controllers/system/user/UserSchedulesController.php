<?php
namespace App\Http\Controllers\system\user;

use App\Http\Controllers\RootController;

use App\Helpers\TaskHelper;
use App\Helpers\TokenHelper;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

class UserschedulesController extends RootController
{
    public $permissions;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->permissions = TaskHelper::getPermissions('user/schedules', $this->user->userGroupRole);
    }
    public function initialize(Request $request)
    {
        if ($this->permissions['action_0'] == 1) {
            $response = [];
            $response['error'] = '';
            $response['permissions'] = $this->permissions;
            return response()->json($response, 200);

        } else {
            return response()->json(['error' => 'ACCESS_DENIED', 'message' => __('messages.ACCESS_DENIED')]);
        }
    }

}
