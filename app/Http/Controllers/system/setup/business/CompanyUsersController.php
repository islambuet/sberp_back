<?php
namespace App\Http\Controllers\system\setup\business;

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

class CompanyUsersController extends RootController
{
    public $permissions;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->permissions = TaskHelper::getPermissions('setup/business/company-users', $this->user->userGroupRole);
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
    public function getItems(Request $request)
    {
        if ($this->permissions['action_0'] == 1) {
            $response = [];
            $response['error'] = '';
            $per_page = $request->per_page ? $request->per_page : 20;
            $company_id = $request->company_id ? $request->company_id : 0;

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

            if ($company_id > 0) {
                $query->where('companies.id', $company_id);
            }
            $results = $query->paginate($per_page)->toArray();
            //page numbers takes autometically
            $response = $results;
            $response['error'] = '';
            return response()->json($response);

        } else {
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }
    }
    public function getItem($itemId, Request $request)
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
    public function saveItems(Request $request)
    {
        $save_token = TokenHelper::getSaveToken($request->save_token, $this->user['id']);
        if (isset($save_token['error']) && strlen($save_token['error']) > 0) {
            return response()->json($save_token);
        }
        $company_id = $request->company_id ? $request->company_id : 0;
        if (!($request->items && is_array($request->items))) {
            return response()->json(['error' => 'VALIDATION_FAILED', 'message' => __('validation.input_not_found')]);

        }
        $items = $request->items;
        //company validation
        $result = DB::table(TABLE_COMPANIES)->select('id')->where('status', SYSTEM_STATUS_ACTIVE)->find($company_id);
        if (!$result) {
            return response()->json(['error' => 'ITEM_NOT_FOUND', 'messages' => __('validation.data_not_found', ['attribute' => 'company_id: ' . $company_id])]);
        }

        $results = DB::table(TABLE_COMPANY_USER_GROUPS)->select('id')->where('company_id', $company_id)->get();
        $company_user_group_ids = [];
        $company_user_group_ids[0] = 0;
        foreach ($results as $result) {
            $company_user_group_ids[$result->id] = $result->id;
        }
        $results = DB::table(TABLE_COMPANY_BRANCHES)->select('id')->where('company_id', $company_id)->get();
        $company_branch_ids = [];
        $company_branch_ids[0] = 0;
        foreach ($results as $result) {
            $company_branch_ids[$result->id] = $result->id;
        }
        $user_ids = [];
        $user_ids[0] = 0;
        foreach ($items as $index => $item) {
            $items[$index]['error'] = '';
            $items[$index]['messages'] = '';
            //input vaidation
            if ((count($item) != 4) || !isset($item['user_id']) || !isset($item['company_user_group_id']) || !isset($item['company_brach_id']) || !isset($item['designation'])) {
                $items[$index]['error'] = 'VALIDATION_FAILED';
                $items[$index]['messages'] = __('Input Missing or Invalid Input');
            }
            //usergroup checking
            if (!$items[$index]['error']) {

                if (!(in_array($item['company_user_group_id'], $company_user_group_ids))) {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('Invalid User Group');
                }
            }
            //brach checking
            if (!$items[$index]['error']) {
                if (!(is_array($item['company_brach_id'])) || (array_diff($item['company_brach_id'], $company_branch_ids))) {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('Invalid Branch');
                }
            }
            //checking duplicate userid
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
        $query->leftJoin(TABLE_COMPANY_USERS . ' as company_users', function ($join) use ($company_id) {
            $join->on('users.id', '=', 'company_users.user_id');
            $join->on('company_users.company_id', '=', DB::raw($company_id));
        });
        $results = $query->get();
        $user_infos = [];
        foreach ($results as $result) {
            $user_infos[$result->user_id] = $result;
        }
        // echo '<pre>';
        // print_r($user_infos);
        // echo '</pre>';
        // die();
        //valdating if need to update/add and
        $changed = false;
        foreach ($items as $index => $item) {
            if (!$items[$index]['error']) {
                if (isset($user_infos[$item['user_id']])) {
                    if ($user_infos[$item['user_id']]->id > 0) {
                        if ($user_infos[$item['user_id']]->status == SYSTEM_STATUS_ACTIVE) {
                            $items[$index]['error'] = 'VALIDATION_FAILED';
                            $items[$index]['messages'] = __('User Already Added');
                        } else {
                            $changed = true;
                        }
                    } else {
                        $changed = true;
                    }
                } else {
                    $items[$index]['error'] = 'VALIDATION_FAILED';
                    $items[$index]['messages'] = __('User Not Found');
                }
            }
        }
        if ($changed) {
            DB::beginTransaction();
            foreach ($items as $index => $item) {
                if (!$items[$index]['error']) {
                    try {
                        $itemNew = $item;
                        unset($itemNew['company_brach_id'], $itemNew['error'], $itemNew['messages']);
                        $itemNew['company_id'] = $company_id;
                        $itemNew['company_branch_ids'] = ',' . implode(',', $item['company_brach_id']) . ',';
                        $itemNew['reason_status_active'] = COMPANY_USER_STATUS_ACTIVE_ADMIN_ADD;
                        $itemNew['status_active_id'] = 0;
                        $itemNew['status'] = SYSTEM_STATUS_ACTIVE;
                        $itemNew['updated_by'] = $this->user['id'];
                        $itemNew['updated_at'] = Carbon::now();

                        if ($user_infos[$item['user_id']]->id > 0) {
                            DB::table(TABLE_COMPANY_USERS)->where('id', $user_infos[$item['user_id']]->id)->update($itemNew);
                            // unset($itemNew['updated_by'],$itemNew['updated_at']);

                            $dataHistory = [];
                            $dataHistory['table_name'] = TABLE_COMPANY_USERS;
                            $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
                            $dataHistory['method'] = __FUNCTION__;
                            $dataHistory['table_id'] = $user_infos[$item['user_id']]->id;
                            $dataHistory['company_id'] = $company_id;
                            $dataHistory['user_id'] = $item['user_id'];
                            $dataHistory['action'] = DB_ACTION_EDIT;
                            //TODO: Unset No change datas
                            $itemOld = $user_infos[$item['user_id']];
                            foreach ($itemOld as $key => $value) {
                                if (!isset($itemNew[$key])) {
                                    unset($itemOld->$key);
                                } else if ($itemNew[$key] == $value) {
                                    unset($itemNew[$key]);
                                    unset($itemOld->$key);
                                }
                            }
                            $dataHistory['data_old'] = json_encode($itemOld);
                            $dataHistory['data_new'] = json_encode($itemNew);
                            $dataHistory['created_at'] = Carbon::now();
                            $dataHistory['created_by'] = $this->user['id'];
                            $this->dBSaveHistory($dataHistory, TABLE_COMPANY_USER_HISTORIES);
                            $items[$index]['messages'] = __('User Updated');
                        } else {
                            DB::table(TABLE_COMPANY_USERS)->insertGetId($itemNew);
                            $items[$index]['messages'] = __('User Added');
                        }
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
