<?php
namespace App\Http\Controllers\company\setup;

use App\Http\Controllers\RootController;

use App\Helpers\CompanyTaskHelper;
use App\Helpers\TokenHelper;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

class CompanyUserInvitationsController extends RootController
{
    public $permissions;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $companyId = request()->route('companyId');
        $this->permissions = CompanyTaskHelper::getPermissions($companyId, 'company-users', $this->user->companyUserGroupRole);
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
    //per_page
    //page

}
