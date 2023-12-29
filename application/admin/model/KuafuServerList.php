<?php

namespace app\admin\model;

use think\Model;

/**
 * 跨服模型类
 */
class KuafuServerList extends Model
{
    //跨服服务器模型
    protected $connection = "db_config_main";
}
