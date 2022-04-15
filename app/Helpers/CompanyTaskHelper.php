<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CompanyTaskHelper
{
    public static $MAX_MODULE_ACTIONS = 4;
    public static function getCompanyUserGroupRoleByUser($user_id)
    {
        $query = DB::table(TABLE_COMPANY_USERS . ' as company_users');
        $query->where('company_users.status', '=', SYSTEM_STATUS_ACTIVE);
        $query->where('company_users.user_id', '=', $user_id);
        $query->select('company_users.company_id');
        $query->addselect('company_users.company_user_group_id');

        $query->join(TABLE_COMPANY_USER_GROUPS . ' as user_groups', 'company_users.company_user_group_id', '=', 'user_groups.id');
        for ($i = 0; $i < self::$MAX_MODULE_ACTIONS; $i++) {
            $query->addselect('user_groups.action_' . $i);
            //$role['action_'.$i]=',';
        }

        $results = $query->get();
        $role = [];
        foreach ($results as $result) {
            $role[$result->company_id] = $result;
        }
        return $role;
    }
    public static function getPermissions($companyId, $url, $companyUserGroupRole) //forApi

    {
        $permissions = [];
        $task = DB::table(TABLE_COMPANY_TASKS)->where('url', $url)->select('id')->first();
        $taskId = $task ? $task->id : 0;
        for ($i = 0; $i < self::$MAX_MODULE_ACTIONS; $i++) {
            //if(strpos($userGroupRole['action_'.$i], ','.$taskId.',')!==false){
            if (isset($companyUserGroupRole[$companyId]) && (strpos($companyUserGroupRole[$companyId]->{'action_' . $i}, ',' . $taskId . ',') !== false)) {
                $permissions['action_' . $i] = 1;
            } else {
                $permissions['action_' . $i] = 0;
            }
        }
        return $permissions;
    }
    public static function getCompanyUserGroupMenu($companyId, $companyUserGroupRole)
    {
        $tree = [];
        $max_level = 0;
        if (isset($companyUserGroupRole[$companyId])) {
            $userGroupRole = $companyUserGroupRole[$companyId];

            $role = [];
            if (strlen($userGroupRole->action_0) > 1) {
                $role = explode(',', trim($userGroupRole->action_0, ','));
            }

            $tasks = DB::table(TABLE_COMPANY_TASKS)
                ->select('id', 'name', 'type', 'parent', 'type', 'url', 'ordering', 'status')
                ->orderBy('ordering', 'ASC')
                ->where('status', SYSTEM_STATUS_ACTIVE)
                ->get()->toArray();
            $children = [];
            foreach ($tasks as $task) {
                $task = (array) $task;
                if ($task['type'] == 'TASK') {
                    if (in_array($task['id'], $role)) {
                        $children[$task['parent']][$task['id']] = $task;
                    }
                } else {
                    $children[$task['parent']][$task['id']] = $task;
                }
            }
            if (isset($children[0])) {
                $tree = self::getCompanyUserGroupSubMenu(1, $max_level, '', '', $children, $children[0]);
            }
        }
        return ['max_level' => $max_level, 'menu' => $tree];
    }
    public static function getCompanyUserGroupSubMenu($level, &$max_level, $parent_class, $prefix, $list, $parent)
    {
        $tree = [];
        foreach ($parent as $key => $element) {
            $element['level'] = $level;
            $element['parent_class'] = $parent_class;
            $element['prefix,'] = $prefix;
            //$tree[] = $element;
            if (isset($list[$element['id']])) {
                $children = self::getCompanyUserGroupSubMenu($level + 1, $max_level, $parent_class . ' parent_' . $element['id'], $prefix . '- ', $list, $list[$element['id']]);
                if ($children) {
                    $element['children'] = $children;
                    $tree[] = $element;
                }
            } else {
                if ($element['type'] == 'TASK') {
                    $tree[] = $element;
                    if ($level > $max_level) {
                        $max_level = $level;
                    }
                }
            }
        }
        return $tree;

    }
}
