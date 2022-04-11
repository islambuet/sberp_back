<?php
namespace App\Http\Controllers\company\setup;

use App\Helpers\CompanyTaskHelper;
use App\Helpers\TokenHelper;
use App\Http\Controllers\RootController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyUserGroupsController extends RootController
{
    public $permissions;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $companyId = request()->route('companyId');
        $this->permissions = CompanyTaskHelper::getPermissions($companyId, 'setup/company-user-groups', $this->user->companyUserGroupRole);
    }
    public function initialize(Request $request)
    {
        if ($this->permissions['action_0'] == 1) {
            $response = [];
            $response['error'] = '';
            $response['permissions'] = $this->permissions;

            return response()->json($response, 200);

        } else {
            return response()->json(['error' => 'ACCESS_DENIED', 'message' => __('messages.ACCESS_DENIED')], 401);
        }
    }
    //per_page
    //page
    public function getItems($companyId, Request $request)
    {
        if ($this->permissions['action_0'] == 1) {
            $response = [];
            $response['error'] = '';
            $per_page = $request->per_page ? $request->per_page : 20;

            $query = DB::table(TABLE_COMPANY_USER_GROUPS . ' as user_groups');
            $query->select('user_groups.*', 'companies.name as company_name');
            $query->orderBy('companies.id', 'DESC');
            $query->orderBy('user_groups.id', 'DESC');

            $query->join(TABLE_COMPANIES . ' as companies', 'user_groups.company_id', '=', 'companies.id');

            $query->where('user_groups.status', '!=', SYSTEM_STATUS_DELETE);
            $query->where('companies.status', '!=', SYSTEM_STATUS_DELETE);
            $query->where('user_groups.company_id', $companyId);

            $results = $query->paginate($per_page)->toArray();
            //page numbers takes autometically
            $response = $results;
            $response['error'] = '';

            return response()->json($response);

        } else {
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }
    }
    public function getItem($companyId, $itemId, Request $request)
    {
        if ($this->permissions['action_0'] == 1) {

            $response = [];
            $response['error'] = '';
            $query = DB::table(TABLE_COMPANY_USER_GROUPS . ' as user_groups');
            $query->select('user_groups.*', 'companies.name as company_name');

            $query->join(TABLE_COMPANIES . ' as companies', 'user_groups.company_id', '=', 'companies.id');

            $query->where('user_groups.status', '!=', SYSTEM_STATUS_DELETE);
            $query->where('companies.status', '!=', SYSTEM_STATUS_DELETE);
            $query->where('user_groups.id', $itemId);
            $query->where('user_groups.company_id', $companyId);

            $result = $query->first();
            if (!$result) {
                return response()->json(['error' => 'ITEM_NOT_FOUND', 'messages' => __('validation.data_not_found', ['attribute' => 'id: ' . $itemId])]);
            }
            $response['data'] = $result;

            return response()->json($response);

        } else {
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }
    }
    public function saveItem($companyId, Request $request)
    {

        $itemOld = [];
        $save_token = TokenHelper::getSaveToken($request->save_token, $this->user['id']);
        if(isset($save_token['error'])&& strlen($save_token['error'])>0){
            return response()->json($save_token);
        }
        $itemId = $request->id ? $request->id : 0;

        $validation_rule = [];
        $validation_rule['name'] = ['required', 'string', 'min:3', 'max:255'];
        // $validation_rule['company_id']=['required', 'numeric'];
        $validation_rule['ordering'] = ['numeric'];
        $validation_rule['status'] = [Rule::in([SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE])];

        $itemNew = $request->item;
        
        $validation = $this->validateInputKeys($itemNew, array_keys($validation_rule));        
        if(isset($validation['error'])&& strlen($validation['error'])>0){
            return response()->json($validation);
        }

        if ($itemId > 0) {
            if ($this->permissions['action_2'] != 1) {
                return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED_EDIT')]);
            }
            $result = DB::table(TABLE_COMPANY_USER_GROUPS)->select(array_keys($validation_rule))->where('company_id', $companyId)->find($itemId);
            if (!$result) {
                return response()->json(['error' => 'ITEM_NOT_FOUND', 'messages' => __('validation.data_not_found', ['attribute' => 'id: ' . $itemId])]);
            }
            $itemOld = $result;
            foreach ($itemOld as $key => $oldValue) {
                if (array_key_exists($key, $itemNew)) {
                    if ($itemOld->$key == $itemNew[$key]) {
                        unset($itemNew[$key]);
                        unset($itemOld->$key);
                        unset($validation_rule[$key]);
                    }
                } else {
                    unset($validation_rule[$key]);
                    unset($itemOld->$key); //no change
                }
            }
            if (!(count($itemNew) > 0)) {
                return response()->json(['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_not_changed')]);
            }

        } else {
            if ($this->permissions['action_1'] != 1) {
                return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED_ADD')]);
            }
        }

        $validation = $this->validateInputValues($itemNew, $validation_rule);
        if(isset($validation['error'])&& strlen($validation['error'])>0){
            return response()->json($validation);
        }

        if (array_key_exists('name', $itemNew)) {
            //no need !itemId because if name same already unset
            $result = DB::table(TABLE_COMPANY_USER_GROUPS)->where('name', $itemNew['name'])->where('company_id', $companyId)->first();
            if ($result) {
                return response()->json(['error' => 'VALIDATION_FAILED', 'messages' => __('validation.data_already_exists', ['attribute' => 'name'])]);
            }
        }
        DB::beginTransaction();
        try {
            if ($itemId > 0) {
                $itemNew['updated_by'] = $this->user['id'];
                $itemNew['updated_at'] = Carbon::now();
                DB::table(TABLE_COMPANY_USER_GROUPS)->where('id', $itemId)->update($itemNew);
                unset($itemNew['updated_by'], $itemNew['updated_at']);

                $dataHistory = [];
                $dataHistory['table_name'] = TABLE_COMPANY_USER_GROUPS;
                $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
                $dataHistory['method'] = __FUNCTION__;
                $dataHistory['table_id'] = $itemId;
                $dataHistory['action'] = DB_ACTION_EDIT;
                $dataHistory['data_old'] = json_encode($itemOld);
                $dataHistory['data_new'] = json_encode($itemNew);
                $dataHistory['created_at'] = Carbon::now();
                $dataHistory['created_by'] = $this->user['id'];
                $this->dBSaveHistory($dataHistory, TABLE_SYSTEM_HISTORIES);
            } else {
                $itemNew['company_id'] = $companyId;
                $itemNew['created_by'] = $this->user['id'];
                $itemNew['created_at'] = Carbon::now();
                $itemNew['id'] = DB::table(TABLE_COMPANY_USER_GROUPS)->insertGetId($itemNew);
                unset($itemNew['created_by'], $itemNew['created_at']);
            }
            TokenHelper::updateSaveToken($save_token);
            DB::commit();

            return response()->json(['error' => '', 'messages' => __('UserGroup Saved Successfully'), 'data' => $itemNew]);
        } catch (\Exception $ex) {
            print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();

            return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
        }
    }
}
