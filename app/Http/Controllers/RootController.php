<?php
namespace App\Http\Controllers;

use App\Helpers\ConfigurationHelper;
use App\Helpers\UserHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

abstract class RootController extends Controller
{
    public $user;
    public function __construct(Request $request)
    {
        ConfigurationHelper::load_config();
        $this->user = UserHelper::getCurrentUser();
    }

    /*
     **$data['table_name']    :Save table name
     **$data['table_id']         :Action id
     **$data['controller']     :Controller Name of the Route
     **$data['method']:         :Funciton Name of the Controller
     **$data['data_old']        :Previous data
     **$data['data_new']        :New Data
     **$data['action']        :Add/Edit/Delete
     **$data['created_at']    :Creating time
     **$data['created_by']    :Action User

     **$tableHistory            :Name of the history table:='system_histories'

     */
    public function dBSaveHistory($data, $tableHistory)
    {
        DB::table($tableHistory)->insertGetId($data);
    }
    public function validateInputKeys($inputs, $keys)
    {
        //checking if any input there
        if (!is_array($inputs)) {

            return ['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_not_found')];
        }
        //checking if any invalid input
        foreach ($inputs as $key => $value) {
            if (!$key || (!in_array($key, $keys))) {
                return ['error' => 'VALIDATION_FAILED', 'messages' => __('validation.input_not_valid', ['attribute' => $key])];
            }
        }
        return [];

    }

    public function validateInputValues($inputs, $validation_rule)
    {
        $validator = Validator::make($inputs, $validation_rule);
        if ($validator->fails()) {
            return ['error' => 'VALIDATION_FAILED', 'messages' => $validator->errors()];
        }
        return [];
    }
}
