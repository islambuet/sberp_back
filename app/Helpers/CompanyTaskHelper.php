<?php
    namespace App\Helpers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    class CompanyTaskHelper
    {
        public static $MAX_MODULE_ACTIONS=4;
        public static function getCompanyUserGroupRoleByUser($user_id)
        {
            $query=DB::table(TABLE_COMPANY_USERS.' as company_users');
            $query->where('company_users.status','=',SYSTEM_STATUS_ACTIVE);    
            $query->where('company_users.user_id','=',$user_id);  
            $query->select('company_users.company_id');
            $query->addselect('company_users.company_user_group_id');
            
            $query->join(TABLE_COMPANY_USER_GROUPS.' as user_groups' , 'company_users.company_user_group_id', '=', 'user_groups.id');
            for($i=0;$i<self::$MAX_MODULE_ACTIONS;$i++)
            {
                $query->addselect('user_groups.action_'.$i);
                //$role['action_'.$i]=',';
            }

            $results=$query->get();
            $role=array();
            foreach ($results as $result){
                $role[$result->company_id]=$result;                
            }
            return $role;
        }
        // public static function getPermissions($url,$userGroupRole)//forApi
        // {
        //     $permissions = array();
        //     $task=DB::table(TABLE_SYSTEM_TASKS)->where('url', $url)->select('id')->first();
        //     $taskId=$task?$task->id:0;
        //     for($i=0; $i<self::$MAX_MODULE_ACTIONS; $i++)
        //     {
        //         if(strpos($userGroupRole['action_'.$i], ','.$taskId.',')!==false){
        //             $permissions['action_'.$i] = 1;
        //         }
        //         else
        //         {
        //             $permissions['action_'.$i]=0;
        //         }
        //     }
        //     return $permissions;
        // }
    }
