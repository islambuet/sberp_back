<?php
namespace App\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Helpers\TaskHelper;
use App\Helpers\CompanyTaskHelper;

class UserHelper {

	public static $loggedUser = null;
	public static function getCurrentUser()
	{
		$user=UserHelper::getLoggedUser();
		if(!$user){
			$user=UserHelper::getGuestUser();
		}		
		$user->userGroupRole=TaskHelper::getUserGroupRole($user->user_group_id);
		$user->companyUserGroupRole=CompanyTaskHelper::getCompanyUserGroupRoleByUser($user->id);		
		return $user;
	}
	public static function getLoggedUser()
	{
		if (!UserHelper::$loggedUser) {
			$user=Auth::guard('sanctum')->user();
			if($user){				
				UserHelper::$loggedUser=$user;
			}						
		}
		return UserHelper::$loggedUser;
	}
	public static function getGuestUser()
	{
		return (object) array('id'=>-2,'user_group_id'=>3);
	}
	public static function getSystemUser()
	{
		return (object) array('id'=>-1,'user_group_id'=>2);
	}
}
