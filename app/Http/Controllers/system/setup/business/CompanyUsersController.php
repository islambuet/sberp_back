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
        $this->permissions=TaskHelper::getPermissions('setup/business/company-users',$this->user->userGroupRole);        
    }
    public function initialize(Request $request)
    {        
        if ($this->permissions['action_0'] == 1){
            $response=array();
            $response['error'] = '';   
            $response['permissions'] = $this->permissions;            
            return response()->json($response, 200);

        }else{
            return response()->json(['error'=>'ACCESS_DENIED','message'=>__('messages.ACCESS_DENIED')], 401);
        }
    }
    //per_page
    //page
    public function getItems(Request $request)
    {
        if ($this->permissions['action_0'] == 1){
            $response=array();
            $response['error'] = '';
            $per_page=$request->per_page?$request->per_page:20;
            $company_id=$request->company_id?$request->company_id:0;
            
            $query=DB::table(TABLE_COMPANY_USER_GROUPS.' as user_groups');
            $query->select('user_groups.*', 'companies.name as company_name');
            $query->orderBy('companies.id', 'DESC');
            $query->orderBy('user_groups.id', 'DESC');

            $query->join(TABLE_COMPANIES.' as companies' , 'user_groups.company_id', '=', 'companies.id');
            
            $query->where('user_groups.status','!=',SYSTEM_STATUS_DELETE);            
            $query->where('companies.status','!=',SYSTEM_STATUS_DELETE);    
            if($company_id>0){
                $query->where('companies.id',$company_id);    
            }        
            $results=$query->paginate($per_page)->toArray();
            //page numbers takes autometically
            $response = $results;
            $response['error'] = '';
            return response()->json($response);

        }else{
            return response()->json(['error'=>'ACCESS_DENIED','messages'=>__('messages.ACCESS_DENIED')]);            
        }
    }
    public function getItem($itemId,Request $request)
    {
        if ($this->permissions['action_0'] == 1){
            
            $response=array();
            $response['error'] = '';
            $query=DB::table(TABLE_COMPANY_USER_GROUPS.' as user_groups');
            $query->select('user_groups.*', 'companies.name as company_name');
            
            $query->join(TABLE_COMPANIES.' as companies' , 'user_groups.company_id', '=', 'companies.id');
            
            $query->where('user_groups.status','!=',SYSTEM_STATUS_DELETE);            
            $query->where('companies.status','!=',SYSTEM_STATUS_DELETE);    
            $query->where('user_groups.id',$itemId);

            $result = $query->first();
            if(!$result){
                return response()->json(['error'=>'ITEM_NOT_FOUND','messages'=>__('validation.data_not_found',['attribute'=>'id: '.$itemId])]);
            }
            $response['data'] = $result;            
            return response()->json($response);

        }else{
            return response()->json(['error'=>'ACCESS_DENIED','messages'=>__('messages.ACCESS_DENIED')]); 
        }        
    }
    public function saveItem(Request $request)
    {
        $save_token=TokenHelper::getSaveToken($request->save_token,$this->user['id']);
        $company_id=$request->company_id?$request->company_id:0;
        if(!($request->items && is_array($request->items))){
            return response()->json(['error'=>'VALIDATION_FAILED','message'=>__('validation.input_not_found')]);

        }
        $items=$request->items;       
        //company validation
        $result = DB::table(TABLE_COMPANIES)->select('id')->where('status',SYSTEM_STATUS_ACTIVE)->find($company_id);       
            if(!$result){
                return response()->json(['error'=>'ITEM_NOT_FOUND','messages'=>__('validation.data_not_found',['attribute'=>'company_id: '.$company_id])]);
            }

        $results=DB::table(TABLE_COMPANY_USER_GROUPS)->select('id')->where('company_id',$company_id)->get();
        $company_user_group_ids=array();  
        $company_user_group_ids[0]=0;
        foreach ($results as $result){
            $company_user_group_ids[$result->id]=$result->id;
        }
        $results=DB::table(TABLE_COMPANY_BRANCHES)->select('id')->where('company_id',$company_id)->get();
        $company_brach_ids=array();  
        $company_brach_ids[0]=0;
        foreach ($results as $result){
            $company_brach_ids[$result->id]=$result->id;
        }
        $user_ids=array();
        $user_ids[0]=0;
        foreach ($items as $index=>$item){
            $items[$index]['error']='';
            $items[$index]['messages']='';
            //input vaidation            
            if((count($item)!=4)|| !isset($item['user_id']) || !isset($item['company_user_group_id'])|| !isset($item['company_brach_id']) || !isset($item['designation'])){
                $items[$index]['error']='VALIDATION_FAILED';
                $items[$index]['messages']=__('Input Missing or Invalid Input');  
            }
            //usergroup checking
            if(!$items[$index]['error'])
            {
                
                if(!(in_array($item['company_user_group_id'],$company_user_group_ids))){
                    $items[$index]['error']='VALIDATION_FAILED';
                    $items[$index]['messages']=__('Invalid User Group');  
                }
            }
            //brach checking
            if(!$items[$index]['error'])
            {
                if(!(is_array($item['company_brach_id'])) || (array_diff($item['company_brach_id'],$company_brach_ids))){
                    $items[$index]['error']='VALIDATION_FAILED';
                    $items[$index]['messages']=__('Invalid Branch');  
                }                
            }
            //checking duplicate userid
            if(!$items[$index]['error'])
            {
                if(isset($user_ids[$item['user_id']])){
                    $items[$index]['error']='VALIDATION_FAILED';
                    $items[$index]['messages']=__('Duplicate Entry');  
                }                
                else{
                    $user_ids[$item['user_id']]=$item['user_id'];
                }
            }

        }

        $query=DB::table(TABLE_USERS.' as users');
        $query->select('company_users.*','users.id as user_id');
        $query->where('users.status',SYSTEM_STATUS_ACTIVE);
        $query->whereIn('users.id',$user_ids);
        $query->leftJoin(TABLE_COMPANY_USERS.' as company_users' , 'company_users.user_id', '=', 'users.id');
        

        //     $query->orderBy('companies.id', 'DESC');
        //     $query->orderBy('user_groups.id', 'DESC');

            // $query->join(TABLE_COMPANIES.' as companies' , 'user_groups.company_id', '=', 'companies.id');
            
            // $query->where('user_groups.status','!=',SYSTEM_STATUS_DELETE);            
            // $query->where('companies.status','!=',SYSTEM_STATUS_DELETE);    
            // if($company_id>0){
            //     $query->where('companies.id',$company_id);    
            // }        
        $results=$query->get();
        $user_infos=array();
        foreach ($results as $result){
            $user_infos[$result->user_id]=$result;
        }
        // echo '<pre>';
        // print_r($user_infos);
        // echo '</pre>';
        // die();
        //valdating if need to update/add and 
        $changed=false;
        foreach ($items as $index=>$item){
            if(!$items[$index]['error'])
            {
                if(isset($user_infos[$item['user_id']])){
                    if($user_infos[$item['user_id']]->id>0){
                        if($user_infos[$item['user_id']]->status=SYSTEM_STATUS_ACTIVE){
                            $items[$index]['error']='VALIDATION_FAILED';
                            $items[$index]['messages']=__('User Already Added');
                        }
                        else{
                            $changed=true;                            
                        }
                    }
                    else{
                        $changed=true;
                    }
                }
                else{
                    $items[$index]['error']='VALIDATION_FAILED';
                    $items[$index]['messages']=__('User Not Found');
                }
            }
        }


        // echo '<pre>';
        // print_r($user_infos);
        // echo '</pre>';
        // die();

        //valdation userids except superadmin and admin ug

        echo '<pre>';
        // print_r($company_user_group_ids);
        // print_r($company_brach_ids);
        print_r($changed);
        print_r($items);
        echo '</pre>';
        die();

        die();
        $itemId=$request->id?$request->id:0;

        
        $validation_rule=array();    
        $validation_rule['name']=['required', 'string','min:3', 'max:255']; 
        $validation_rule['company_id']=['required', 'numeric']; 
        $validation_rule['ordering']=['numeric']; 
        $validation_rule['status']=[Rule::in([SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE])]; 

        $itemNew=$request->item;
        $this->validateInputKeys($itemNew,array_keys($validation_rule));
        
        

        if($itemId>0) {
            if($this->permissions['action_2']!=1) {
                return response()->json(['error'=>'ACCESS_DENIED','messages'=>__('messages.ACCESS_DENIED_EDIT')]);
            }        
            $result = DB::table(TABLE_COMPANY_USER_GROUPS)->select(array_keys($validation_rule))->find($itemId);       
            if(!$result){
                return response()->json(['error'=>'ITEM_NOT_FOUND','messages'=>__('validation.data_not_found',['attribute'=>'id: '.$itemId])]);
            }
            $itemOld=$result;
            foreach($itemOld as $key=>$oldValue){
                if($key=='company_id'){
                    if(array_key_exists($key,$itemNew)){
                        if($itemNew[$key]!=$oldValue){
                            return response()->json(['error'=>'VALIDATION_FAILED','messages'=>__('Company Cannot be Changed')]);
                        }
                    }
                    else{
                        $itemNew[$key]=$oldValue;
                    }                    
                }
                else if(array_key_exists($key,$itemNew)){
                    if($itemOld->$key==$itemNew[$key]){
                        unset($itemNew[$key]);
                        unset($itemOld->$key);
                        unset($validation_rule[$key]);
                    } 
                }
                else{
                    unset($validation_rule[$key]);
                    unset($itemOld->$key); //no change
                }
            }
            if(!(count($itemNew)>1)){
                return response()->json(['error'=>'VALIDATION_FAILED','messages'=>__('validation.input_not_changed')]);
            }
            
        } 
        else{
            if($this->permissions['action_1']!=1) {
                return response()->json(['error'=>'ACCESS_DENIED','messages'=>__('messages.ACCESS_DENIED_ADD')]);
            }  
        }
        $this->validateInputValues($itemNew,$validation_rule);       
        if(array_key_exists('name',$itemNew)){            
            //no need !itemId because if name same already unset
            $result = DB::table(TABLE_COMPANY_USER_GROUPS)->where('name', $itemNew['name'])->where('company_id',$itemNew['company_id'])->first();
            if($result){
                return response()->json(['error'=>'VALIDATION_FAILED','messages'=>__('validation.data_already_exists',['attribute'=>'name'])]);
            }
        }     
        if(array_key_exists('company_id',$itemNew)){            
            //no need !itemId because if name same already unset
            $result = DB::table(TABLE_COMPANIES)->where('id',$itemNew['company_id'])->first();
            if(!$result){
                return response()->json(['error'=>'VALIDATION_FAILED','messages'=>__('Invalid Company')]);
            }
        }   
        DB::beginTransaction();
        try{
            if($itemId>0){
                $itemNew['updated_by']=$this->user['id'];
                $itemNew['updated_at']=Carbon::now();
                DB::table(TABLE_COMPANY_USER_GROUPS)->where('id',$itemId)->update($itemNew);                
                unset($itemNew['updated_by'],$itemNew['updated_at']);
                
                $dataHistory=array();
                $dataHistory['table_name']=TABLE_COMPANY_USER_GROUPS;
                $dataHistory['controller']=(new \ReflectionClass(__CLASS__))->getShortName();
                $dataHistory['method']=__FUNCTION__;
                $dataHistory['table_id']=$itemId;
                $dataHistory['action']=DB_ACTION_EDIT;
                $dataHistory['data_old']=json_encode($itemOld);
                $dataHistory['data_new']=json_encode($itemNew);
                $dataHistory['created_at']=Carbon::now();
                $dataHistory['created_by']=$this->user['id'];
                $this->dBSaveHistory($dataHistory,TABLE_SYSTEM_HISTORIES);
            } else {
                $itemNew['created_by']=$this->user['id'];
                $itemNew['created_at']=Carbon::now();
                $itemNew['id'] = DB::table(TABLE_COMPANY_USER_GROUPS)->insertGetId($itemNew);                
                unset($itemNew['created_by'],$itemNew['created_at']);
            }            
            TokenHelper::updateSaveToken($save_token);
            DB::commit();
            return response()->json(['error' => '','messages'=>__('UserGroup Saved Successfully'),'data' =>$itemNew]);            
        } catch (\Exception $ex) {
            print_r($ex);
            // ELSE rollback & throw exception
            DB::rollback();
            return response()->json(['error' => 'SERVER_ERROR', 'messages'=>__('messages.SERVER_ERROR')]);
        }
    }
}

