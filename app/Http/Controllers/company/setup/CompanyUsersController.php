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

class CompanyUsersController extends RootController
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
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
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

            $query = DB::table(TABLE_COMPANY_USERS . ' as company_users');
            $query->select('company_users.*');
            $query->join(TABLE_COMPANIES . ' as companies', 'company_users.company_id', '=', 'companies.id');
            $query->addSelect('companies.name as company_name');

            $query->join(TABLE_COMPANY_USER_GROUPS . ' as user_groups', 'company_users.company_user_group_id', '=', 'user_groups.id');
            $query->addSelect('user_groups.name as user_group_name');

            $query->join(TABLE_USERS . ' as users', 'company_users.user_id', '=', 'users.id');
            $query->addSelect('users.first_name', 'users.last_name');

            $query->orderBy('companies.id', 'DESC');
            $query->orderBy('users.id', 'DESC');

            $query->where('company_users.status', '=', SYSTEM_STATUS_ACTIVE);
            $query->where('companies.status', '!=', SYSTEM_STATUS_DELETE);
            $query->where('user_groups.status', '!=', SYSTEM_STATUS_DELETE);

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

    public function saveItems($companyId, Request $request)
    {
        if ($this->permissions['action_2'] != 1) {
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }

        $save_token = TokenHelper::getSaveToken($request->save_token, $this->user['id']);
        if (isset($save_token['error']) && strlen($save_token['error']) > 0) {
            return response()->json($save_token);
        }
        if (!($request->items && is_array($request->items))) {
            return response()->json(['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_not_found')]);

        }
        $items = $request->items;

        $results = DB::table(TABLE_COMPANY_USER_GROUPS)->select('id')->where('company_id', $companyId)->get();
        $company_user_group_ids = [];
        $company_user_group_ids[0] = 0;
        foreach ($results as $result) {
            $company_user_group_ids[$result->id] = $result->id;
        }
        $results = DB::table(TABLE_COMPANY_BRANCHES)->select('id')->where('company_id', $companyId)->get();
        $company_branch_ids = [];
        $company_branch_ids[0] = 0;
        foreach ($results as $result) {
            $company_branch_ids[$result->id] = $result->id;
        }
        $user_ids = [];
        $user_ids[0] = 0;
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                return response()->json(['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_format_invalid')]);
            }
            $items[$index]['error'] = '';
            $items[$index]['messages'] = '';
            //user_id mandatory
            if (!isset($item['user_id'])) {
                $items[$index]['error'] = 'VALIDATION_FAILED';
                $items[$index]['messages'] = __('validation.input_missing');
            } else {
                //atleast one other input required
                if (count($item) < 2) {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('validation.input_missing');
                } else {
                    foreach ($item as $key => $value) {
                        //checking each key is valid input
                        if (!(in_array($key, ['user_id', 'company_user_group_id', 'company_branch_ids', 'designation', 'status']))) {
                            $items[$index]['error'] = 'VALIDATION_FAILED';
                            $items[$index]['messages'] = __('validation.input_not_valid', ['attribute' => $key]);
                            break;
                        }
                        //checking usergroup value is valid
                        else if (($key == 'company_user_group_id') && (!($value > 0) || (!(in_array($value, $company_user_group_ids))))) {
                            $items[$index]['error'] = 'VALIDATION_FAILED';
                            $items[$index]['messages'] = __('Invalid User Group');
                        }
                        //checking branch, TODO: 0 accepted Means no branch
                        else if (($key == 'company_branch_ids') && (!(is_array($value)) || (array_diff($value, $company_branch_ids)))) {
                            $items[$index]['error'] = 'VALIDATION_FAILED';
                            $items[$index]['messages'] = __('Invalid Branch');
                        }
                    }
                }
            }

            // checking duplicate userid
            if (!$items[$index]['error']) {
                if (isset($user_ids[$item['user_id']])) {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('Duplicate Entry');
                } else {
                    $user_ids[$item['user_id']] = $item['user_id'];
                }
            }
        }

        $query = DB::table(TABLE_USERS . ' as users');
        $query->select('company_users.*', 'users.id as user_id');
        $query->where('users.status', SYSTEM_STATUS_ACTIVE);
        $query->whereIn('users.id', $user_ids);
        $query->join(TABLE_COMPANY_USERS . ' as company_users', function ($join) use ($companyId) {
            $join->on('users.id', '=', 'company_users.user_id');
            $join->where('company_users.company_id', '=', $companyId);
            $join->where('company_users.status', '=', SYSTEM_STATUS_ACTIVE);
        });
        $results = $query->get();
        $user_infos = [];
        foreach ($results as $result) {
            $user_infos[$result->user_id] = $result;
        }

        //valdating if need to update
        $changed = false;
        foreach ($items as $index => $item) {
            if (!$items[$index]['error']) {
                if (!(isset($user_infos[$item['user_id']]))) {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('User Does not belongs to this company');
                } else {
                    $itemOld = $itemNew = [];
                    $key = 'company_user_group_id';
                    if (isset($item[$key]) && ($item[$key] != $user_infos[$item['user_id']]->$key)) {
                        $itemNew[$key] = $item[$key];
                        $itemOld[$key] = $user_infos[$item['user_id']]->$key;
                    }
                    $key = 'designation';
                    if (isset($item[$key]) && ($item[$key] != $user_infos[$item['user_id']]->$key)) {
                        $itemNew[$key] = $item[$key];
                        $itemOld[$key] = $user_infos[$item['user_id']]->$key;
                    }
                    $key = 'company_branch_ids';

                    if (isset($item[$key])) {
                        $keyvalue = ',' . implode(',', $item[$key]) . ',';
                        if (isset($item[$key]) && ($keyvalue != $user_infos[$item['user_id']]->$key)) {
                            $itemNew[$key] = $keyvalue;
                            $itemOld[$key] = $user_infos[$item['user_id']]->$key;
                        }
                    }
                    $key = 'status';
                    if (isset($item[$key])) {
                        if ($item[$key] != SYSTEM_STATUS_ACTIVE) {
                            if ($item[$key] == SYSTEM_STATUS_INACTIVE) {
                                $itemNew[$key] = $item[$key];
                                $itemNew['reason_status_inactive'] = COMPANY_USER_STATUS_INACTIVE_OWNER_REMOVE;
                                $itemNew['status_inactive_id'] = 0;
                                $itemOld[$key] = $user_infos[$item['user_id']]->$key;

                            } else {
                                $items[$index]['error'] = 'VALIDATION_FAILED';
                                $items[$index]['messages'] = __('validation.input_not_valid', ['attribute' => $key]);
                            }
                        }

                    }
                    if ($itemNew) {
                        $items[$index]['itemOld'] = $itemOld;
                        $items[$index]['itemNew'] = $itemNew;
                        $changed = true;
                    } else {
                        $items[$index]['error'] = 'VALIDATION_FAILED';
                        $items[$index]['messages'] = __('validation.input_not_changed');
                    }
                }
            }
        }
        if ($changed) {
            DB::beginTransaction();
            foreach ($items as $index => $item) {
                if (!$items[$index]['error']) {
                    try {
                        $itemOld = $items[$index]['itemOld'];
                        $itemNew = $items[$index]['itemNew'];
                        $itemNew['updated_by'] = $this->user['id'];
                        $itemNew['updated_at'] = Carbon::now();

                        DB::table(TABLE_COMPANY_USERS)->where('id', $user_infos[$item['user_id']]->id)->update($itemNew);
                        unset($itemNew['updated_by'], $itemNew['updated_at']);

                        $dataHistory = [];
                        $dataHistory['table_name'] = TABLE_COMPANY_USERS;
                        $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
                        $dataHistory['method'] = __FUNCTION__;
                        $dataHistory['table_id'] = $user_infos[$item['user_id']]->id;
                        $dataHistory['company_id'] = $companyId;
                        $dataHistory['user_id'] = $item['user_id'];
                        $dataHistory['action'] = DB_ACTION_EDIT;

                        $dataHistory['data_old'] = json_encode($itemOld);
                        $dataHistory['data_new'] = json_encode($itemNew);
                        $dataHistory['created_at'] = Carbon::now();
                        $dataHistory['created_by'] = $this->user['id'];
                        $this->dBSaveHistory($dataHistory, TABLE_COMPANY_USER_HISTORIES);
                        $items[$index]['messages'] = __('User Updated');

                    } catch (\Exception $ex) {

                        DB::rollback();
                        return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
                    }
                }
            }
            TokenHelper::updateSaveToken($save_token);
            DB::commit();
        }
        return $items;
    }
}
