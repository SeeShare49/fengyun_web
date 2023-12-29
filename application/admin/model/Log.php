<?php

namespace app\admin\model;

use think\Db;
use think\Model;


class Log extends Model
{
    //

    protected $connection = 'db_config_log';

    protected $pk = 'logid';


    public function unionAll($union)
    {
        return parent::unionAll($union); // TODO: Change the autogenerated stub
    }

    static function union_list($start_date, $end_date)
    {
//        $filed = "logid,serverId,serverName,userId,playerName,moduleId,moduleName,value,actionName,logtime";
        $filed = "logid,serverId";
        $base_table = 'log' . date("Ymd", strtotime($start_date));


        // select logid,serverId from log20210315 where 1=1   select logid,serverId from log20210316 where 1=1 


        $arr = explode('|', self::getUnionAllSql($start_date, $end_date));
        var_dump($arr);
    }

    static function getUnionAllSql($start_date, $end_date)
    {
        $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
        $where_str = '1=1 ';
        $filed = "logid,serverId";
        $unionSql = '';
        for ($i = 1; $i <= $diff; $i++) {
            $union_table = 'log' . date("Ymd", strtotime("+$i day", strtotime($start_date)));
            $unionSql .= 'select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . '|';

        }
        //$union = substr($unionSql, 0, strlen($unionSql) - 1);
        $union = substr($unionSql, 0, -1);
        dump($union);
        return $union;
    }
}
