<?php

namespace app\admin\model;

use think\Model;

class UserInfo extends Model
{
    //玩家用户模型
    protected $connection = "db_config_main";
    protected $pk = 'UserID';

//    protected $resultSetType= 'collection';


    public static function init()
    {
//        UserInfo::event('before_insert', function ($user) {
//            if ($user->BanFlag == -1)
//                return false;
//        });

        //UserInfo::event('before_insert', '\think\model\concern\ModelEvent::beforeInsert');

//        UserInfo::beforeInsert(function ($user) {
//            if ($user->BanFlag == -1)
//                return false;
//        });
//
//        UserInfo::event('before_update', function ($user) {
//            if ($user->BanFlag != -1)
//                return false;
//        });
//
//        UserInfo::beforeUpdate(function ($user) {
//            if ($user->BanFlag != -1)
//                return false;
//        });


//        self::beforeUpdate(function ($user) {
//            if ($user->BanFlag != 1)
//                return false;
//        });



        self::afterDelete(function ($user){
            if(method_exists($user,'after_delete'))
            {

            }
        });
    }
}


