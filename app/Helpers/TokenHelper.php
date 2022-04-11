<?php
    namespace App\Helpers;
    use Illuminate\Support\Facades\DB;    
    use Carbon\Carbon;

    class TokenHelper
    {
        public static function getSaveToken($token,$user_id)
        {
            $save_token=['id'=>0,'token'=>$token,'user_id'=>$user_id];
            if(!$token){
                return ['error'=>'VALIDATION_FAILED','messages'=>__('validation.save_token_missing')];
            }
            else{                
                if (!ctype_alnum( str_replace(['-','_'], '', $token) ) ) {
                    return ['error'=>'VALIDATION_FAILED','messages'=>__('validation.save_token_invalid')];
                }
            }
            $result = DB::table(TABLE_SYSTEM_TOKENS)->where('user_id', $user_id)->first();
            if($result)
            {
                if($result->token==$token)
                {
                    return ['error'=>'DATA_ALREADY_SAVED','messages'=>__('validation.save_token_data_already_saved')];
                }
                else
                {
                    $save_token['id']=$result->id;
                }
            }
            return $save_token;            
        }
        public static function updateSaveToken($save_token)
        {
            if($save_token['id']>0)
            {
                $itemNew=array();
                $itemNew['token']=$save_token['token'];
                $itemNew['revision_count']=DB::raw('revision_count + 1');                
                $itemNew['updated_at']=Carbon::now();
                DB::table(TABLE_SYSTEM_TOKENS)->where('id',$save_token['id'])->update($itemNew);
            }
            else
            {
                $itemNew=array();
                $itemNew['user_id']=$save_token['user_id'];
                $itemNew['token']=$save_token['token'];                
                $itemNew['updated_at']=Carbon::now();
                $id = DB::table(TABLE_SYSTEM_TOKENS)->insertGetId($itemNew);
            }
        }

    }
