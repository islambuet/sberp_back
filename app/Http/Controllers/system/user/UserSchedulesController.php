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
            return response()->json(['error' => 'ACCESS_DENIED', 'messages' => __('messages.ACCESS_DENIED')]);
        }
    }
    public function addItems(Request $request)
    {
        if ($this->permissions['action_1'] != 1) {
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

        $validation_rule = [];
        $validation_rule['start_time'] = ['required', 'string']; //TODO time format
        $validation_rule['end_time'] = ['required', 'string']; //TODO time format
        $validation_rule['occupation_id'] = ['required', 'numeric', 'gt:0']; //TODO check if exists in db
        $validation_rule['hourly_rate'] = ['required', 'numeric', 'gt:0']; //TODO check if exists in db
        $validation_rule['address'] = ['string'];
        $validation_rule['long'] = ['numeric', 'gt:0'];
        $validation_rule['lat'] = ['numeric', 'gt:0'];
        $validation_rule['company_ids'] = ['array']; //TODO check if all ids valid
        $validation_rule['repeat_type'] = [Rule::in([REPEAT_TYPE_NO_REPEAT, REPEAT_TYPE_DAILY, REPEAT_TYPE_WEEKLY, REPEAT_TYPE_MONTHLY])];
        $validation_rule['note'] = ['string'];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                return response()->json(['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_format_invalid')]);
            }
            $items[$index]['error'] = '';
            $items[$index]['messages'] = '';
            $validation = $this->validateInputKeys($item, array_keys($validation_rule));
            if (isset($validation['error']) && strlen($validation['error']) > 0) {
                $items[$index] = array_merge($items[$index], $validation);
                continue;
            }
            $validation = $this->validateInputValues($item, $validation_rule);
            if (isset($validation['error']) && strlen($validation['error']) > 0) {
                $items[$index] = array_merge($items[$index], $validation);
            }
        }
        $timeNow = Carbon::now();
        DB::beginTransaction();
        foreach ($items as $index => $item) {
            if (!$items[$index]['error']) {
                try {
                    $itemNew = $item;
                    unset($itemNew['company_ids'], $itemNew['error'], $itemNew['messages']);

                    $itemNew['user_id'] = $this->user['id'];
                    if (isset($item['company_ids'])) {
                        $itemNew['company_ids'] = ',' . implode(',', $item['company_ids']) . ',';
                    }
                    $itemNew['created_at'] = $timeNow->copy();
                    $id = DB::table(TABLE_USER_SCHEDULES)->insertGetId($itemNew);
                    $items[$index]['id'] = $id;
                    $items[$index]['messages'] = __('Schedule Created');
                } catch (\Exception $ex) {

                    DB::rollback();
                    return response()->json(['error' => 'SERVER_ERROR', 'messages' => __('messages.SERVER_ERROR')]);
                }
            }
        }
        TokenHelper::updateSaveToken($save_token);
        DB::commit();

        return $items;

    }

}
