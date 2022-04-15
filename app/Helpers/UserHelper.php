<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Helpers\TaskHelper;
use App\Helpers\CompanyTaskHelper;

class UserHelper
{

    public static $loggedUser = null;
    public static function getCurrentUser()
    {
        $user = UserHelper::getLoggedUser();
        if (!$user) {
            $user = UserHelper::getGuestUser();
        }
        $user->userGroupRole = TaskHelper::getUserGroupRole($user->user_group_id);
        $user->companyUserGroupRole = CompanyTaskHelper::getCompanyUserGroupRoleByUser($user->id);

        return $user;
    }
    public static function getLoggedUser()
    {
        if (!UserHelper::$loggedUser) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                UserHelper::$loggedUser = $user;
            }
        }

        return UserHelper::$loggedUser;
    }
    public static function getGuestUser()
    {
        return (object) ['id' => -2, 'user_group_id' => 3];
    }
    public static function getSystemUser()
    {
        return (object) ['id' => -1, 'user_group_id' => 2];
    }
    public static function getUserCompanies($user_id)
    {
        $query = DB::table(TABLE_COMPANY_USERS . ' as company_users');
        $query->where('company_users.status', '=', SYSTEM_STATUS_ACTIVE);
        $query->where('company_users.user_id', '=', $user_id);
        $query->select('company_users.company_id');
        $query->join(TABLE_COMPANIES . ' as companies', 'company_users.company_id', '=', 'companies.id');
        $query->where('companies.status', '=', SYSTEM_STATUS_ACTIVE);
        $query->addselect('companies.name as company_name');
        $results = $query->get();
        return $results;
    }
}
