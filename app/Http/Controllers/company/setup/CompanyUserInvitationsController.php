<?php
namespace App\Http\Controllers\company\setup;

use App\Http\Controllers\RootController;

use App\Helpers\CompanyTaskHelper;
use App\Helpers\TokenHelper;
use App\Helpers\ConfigurationHelper;

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
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }
    }
    //per_page
    //page
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
            $items[$index]['error'] = '';
            $items[$index]['messages'] = '';
            //input vaidation
            if ((count($item) != 4) || !isset($item['user_id']) || !isset($item['company_user_group_id']) || !isset($item['company_branch_ids']) || !isset($item['designation'])) {
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
                if (!(is_array($item['company_branch_ids'])) || (array_diff($item['company_branch_ids'], $company_branch_ids))) {
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
        //user and users of company
        $query = DB::table(TABLE_USERS . ' as users');
        $query->select('company_users.*', 'users.id as user_id');
        $query->where('users.status', SYSTEM_STATUS_ACTIVE);
        $query->whereIn('users.id', $user_ids);
        $query->leftJoin(TABLE_COMPANY_USERS . ' as company_users', function ($join) use ($companyId) {
            $join->on('users.id', '=', 'company_users.user_id');
            $join->where('company_users.company_id', '=', $companyId);
        });

        $results = $query->get();
        $user_infos = [];
        foreach ($results as $result) {
            $user_infos[$result->user_id] = $result;
        }

        //invited user

        $query = DB::table(TABLE_COMPANY_USER_INVITATIONS . ' as user_invitations');
        $query->select('user_invitations.*');
        $query->where('user_invitations.status', SYSTEM_STATUS_PENDING);
        $query->where('user_invitations.company_id', $companyId);
        $query->whereIn('user_invitations.user_id', $user_ids);

        $results = $query->get();
        $invited_user_infos = [];
        foreach ($results as $result) {
            $invited_user_infos[$result->user_id] = $result;
        }

        //also query already invited

        //valdating if need to update/add
        $changed = false;
        foreach ($items as $index => $item) {
            if (!$items[$index]['error']) {
                if (isset($user_infos[$item['user_id']])) {
                    if ($user_infos[$item['user_id']]->id > 0) { //here id is company user table id.
                        if ($user_infos[$item['user_id']]->status == SYSTEM_STATUS_ACTIVE) {
                            $items[$index]['error'] = 'VALIDATION_FAILED';
                            $items[$index]['messages'] = __('User Already Belongs to this company');
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
                        unset($itemNew['company_branch_ids'], $itemNew['error'], $itemNew['messages']);
                        $itemNew['company_id'] = $companyId;
                        $itemNew['company_branch_ids'] = ',' . implode(',', $item['company_branch_ids']) . ',';
                        $itemNew['status'] = SYSTEM_STATUS_PENDING;
                        $itemNew['invited_by'] = $this->user['id'];
                        $itemNew['invited_at'] = Carbon::now();
                        $itemNew['expires_at'] = Carbon::now()->addSeconds(ConfigurationHelper::get_company_user_invitation_expire_time());

                        if (isset($invited_user_infos[$item['user_id']])) {
                            $itemNew['revision_count'] = DB::raw('revision_count+1');
                            DB::table(TABLE_COMPANY_USER_INVITATIONS)->where('id', $invited_user_infos[$item['user_id']]->id)->update($itemNew);

                            unset($itemNew['revision_count']);
                            $dataHistory = [];
                            $dataHistory['table_name'] = TABLE_COMPANY_USER_INVITATIONS;
                            $dataHistory['controller'] = (new \ReflectionClass(__CLASS__))->getShortName();
                            $dataHistory['method'] = __FUNCTION__;
                            $dataHistory['table_id'] = $invited_user_infos[$item['user_id']]->id;
                            $dataHistory['action'] = DB_ACTION_EDIT;
                            //TODO: Unset No change datas
                            $itemOld = $invited_user_infos[$item['user_id']];
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
                            $this->dBSaveHistory($dataHistory, TABLE_SYSTEM_HISTORIES);
                            $items[$index]['messages'] = __('User Invite Updated');
                        } else {
                            DB::table(TABLE_COMPANY_USER_INVITATIONS)->insertGetId($itemNew);
                            $items[$index]['messages'] = __('User Invited');
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
