<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\UserHelper;

class loggedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user=UserHelper::getLoggedUser();
        if(!$user){
            return response()->json(['error' => "USER_SESSION_EXPIRED",'errorMessage'=>__('messages.user_session_expired')]);
        }  
        //bellow condition will not happen      
        else if(is_null($user['email_verified_at'])){
            return response()->json(['error' => "EMAIL_NOT_VERIFIED",'errorMessage'=>__('messages.email_not_verified')]);
        }
        return $next($request);
    }
}
