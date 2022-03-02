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
    }
