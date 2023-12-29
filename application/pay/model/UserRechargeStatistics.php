<?php

namespace app\pay\model;

use think\Model;

class UserRechargeStatistics extends Model
{
    //用户充值统计模型
    protected $connection = 'db_config_main';
    protected $pk = 'user_id';
}
