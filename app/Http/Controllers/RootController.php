<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Helpers\ConfigurationHelper;
use App\Helpers\UserHelper;

abstract class RootController extends Controller
{
    public $user;
    public function __construct(Request $request)
    {
        ConfigurationHelper::load_config();                
        $this->user=UserHelper::getCurrentUser();        
    }
   
    /*
	**$data['table_name']	:Save table name
	**$data['table_id']	 	:Action id
	**$data['controller'] 	:Controller Name of the Route
	**$data['method']: 		:Funciton Name of the Controller
	**$data['data_old']		:Previous data
	**$data['data_new']		:New Data
	**$data['action']		:Add/Edit/Delete
	**$data['created_at']	:Creating time
    **$data['created_by']	:Action User

	**$tableHistory			:Name of the history table:='system_histories'

	*/
    public function dBSaveHistory($data,$tableHistory){        
        DB::table($tableHistory)->insertGetId($data);
    }
}
