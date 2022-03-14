<?php
    namespace App\Helpers;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    class TaskHelper
    {
        public static $MAX_MODULE_ACTIONS=4;
        public static function getUserGroupRole($user_group_id)
        {
            $role=array();
            $query=DB::table(TABLE_USER_GROUPS);
            $query->where('id', $user_group_id);
            for($i=0;$i<self::$MAX_MODULE_ACTIONS;$i++)
            {
                $query->addselect('action_'.$i);
                $role['action_'.$i]=',';
            }
            $userGroup=$query->first();
            if($userGroup) {
                for($i=0;$i<self::$MAX_MODULE_ACTIONS;$i++) {
                    $role['action_'.$i]=$userGroup->{'action_'.$i};
                }
            }
            return $role;
        }
        public static function getPermissions($url,$userGroupRole)//forApi
        {
            $permissions = array();
            $task=DB::table(TABLE_SYSTEM_TASKS)->where('url', $url)->select('id')->first();
            $taskId=$task?$task->id:0;
            for($i=0; $i<self::$MAX_MODULE_ACTIONS; $i++)
            {
                if(strpos($userGroupRole['action_'.$i], ','.$taskId.',')!==false){
                    $permissions['action_'.$i] = 1;
                }
                else
                {
                    $permissions['action_'.$i]=0;
                }
            }
            return $permissions;
        }
    }
