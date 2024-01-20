<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use AlibabaCloud\SDK\Cdn\V20180510\Models\PushObjectCacheRequest;
use think\Db;

return [
    error_reporting(E_ERROR | E_WARNING | E_PARSE),
];


\think\facade\Route::domain('dl.52yiwan.cn', 'admin');


/**
 * 获取数据库中的配置列表
 * @param int $c
 * @return array 配置数组
 * $c=1 前台配置，$c=2后台配置
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_db_config($c = 1)
{
    $data = db('config')->where('status', 1);
    if ($c === 1) {
        $data = $data->where('module', 'in', '0,1');
    } else if ($c === 2) {
        $data = $data->where('module', 'in', '0,2');
    }
    $data = $data->field('type,name,value')->select();

    $config = array();
    if ($data && is_array($data)) {
        foreach ($data as $value) {
            //解析数组
            if ($value['type'] == 3) {
                $array = preg_split('/[,;\r\n]+/', trim($value['value'], ",;\r\n"));
                if (strpos($value['value'], ':')) {
                    $value['value'] = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value['value'][$k] = $v;
                    }
                } else {
                    $value['value'] = $array;
                }
            }
            $config[$value['name']] = $value['value'];
        }
    }
    return $config;
}


function getParam($name, $default = '')
{
    return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
}

/**
 * 动态配置链接数据库
 * @param $server_id
 * @return mixed
 * @throws \think\Exception
 */
/* function dbConfig($server_id)
{
    //@var TYPE_NAME $db_config
    $db_config = Db::connect([
     'type' => 'mysql',
     'hostname' => '121.40.166.20',
     'database' => 'cq_game' . $server_id,
     'username' => 'root',
     'password' => 'yinhe123',
     ]);
    return $db_config;
} */
function dbConfig($server_id,$info=array() )
{
    if(!$info)
    {
        $fields = 'db_ip_w,db_port_w,db_database_name,db_username_w,db_password_w';
        $info = \app\admin\model\ServerList::table('server_list')->field($fields)->where('id','=',$server_id)->find();
    }
    if (!$info) 
    {
        $db_config = null;
        return $db_config ;
    }
    $db_config = Db::connect([
        'type' => 'mysql',
        'hostname' =>$info['db_ip_w'],
        'hostport' => $info['db_port_w'],
        'database' => $info['db_database_name'],
        'username' => $info['db_username_w'],
        'password' => $info['db_password_w'],
    ]); 
    return $db_config;
}

/**
 * 动态配置链接数据库(读库)
 * @param $server_id
 * @return mixed
 * @throws \think\Exception
 */
/* function dbConfigByReadBase($server_id)
{
    //@var TYPE_NAME $db_config
    $db_config = Db::connect([
        'type' => 'mysql',
        'hostname' => '121.40.166.20',
        'database' => 'cq_game' . $server_id,
        'username' => 'root',
        'password' => 'yinhe123',
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ]);
    return $db_config;
}
 */
function dbConfigByReadBase($server_id,$info=array())
{
    if(!$info)
    {
        $fields = 'db_ip_r,db_port_r,db_database_name,db_username_r,db_password_r';
        $info = \app\admin\model\ServerList::table('server_list')->field($fields)->where('id','=',$server_id)->find();
    }
    if (!$info)
    {
        $db_config = null;
        return $db_config ;
    }
    //@var TYPE_NAME $db_config
    $db_config = Db::connect([
        'type' => 'mysql',
        'hostname' =>$info['db_ip_r'],
        'hostport' => $info['db_port_r'],
        'database' => $info['db_database_name'],
        'username' => $info['db_username_r'],
        'password' => $info['db_password_r'],
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ]);
    return $db_config;
}

/**
 * 日志库读取配置
 * @param $date
 * @return TYPE_NAME
 * @throws \think\Exception
 */
/* function dbLogConfig($date)
{
    //@var TYPE_NAME $db_config
    $db_config = Db::connect([
        'type' => 'mysql',
        'hostname' => '121.40.166.20',
        'database' => 'cq_log.Log' . $date,
        'username' => 'root',
        'password' => 'yinhe123',
    ]);
    return $db_config;
} */


/**
 * 截取字符串
 * @param $str
 * @param $cut_len
 * @return mixed|string
 */
function cutStr($str, $cut_len)
{
    //*截取一定长度的字符串，确保截取后字符串不出乱码
    $str_len = strlen($str);
    if ($cut_len > $str_len) return $str;//字符串长度小于规定字数时,返回字符串本身
    //初始不是汉字的字符数
    $not_china_num = 0;
    for ($i = 0; $i < $cut_len; $i++) {
        if (ord(substr($str, $i, 1)) <= 128) {
            $not_china_num++;
        }
    }

    if (($cut_len % 2 == 1) && ($not_china_num % 2 == 0))//如果要截取奇数个字符，所要截取长度范围内的字符必须含奇数个非汉字，否则截取的长度加一
    {
        $cut_len++;
    }
    if (($cut_len % 2 == 0) && ($not_china_num % 2 == 1))//如果要截取偶数个字符，所要截取长度范围内的字符必须含偶数个非汉字，否则截取的长度加一
    {
        $cut_len++;
    }
    return strlen($str) > $cut_len ? substr($str, 0, $cut_len) . "..." : $str;
}

/**
 * 获取玩家等级
 * @param $user_id 用户ID
 * @param $server_id 服务器ID
 * @return string
 * @throws \think\Exception
 */
function get_level($user_id, $server_id)
{
    $level = "";
    if (!isset($user_id) || $user_id == 0)
        return $level;

    return dbConfigByReadBase($server_id)
        ->table('player')
        ->where('account_id', '=', $user_id)
        ->column('level');
}

/**
 * 获取用户角色昵称
 * @param $user_id 用户ID
 * @param $server_id 服务器ID
 * @return string
 * @throws \think\Exception
 */
function get_player_name($user_id, $server_id)
{
    $player_name = "";
    /**
     * 特殊处理，获取真实服务器ID 兼容合服情况
     **/
    $info = \app\admin\model\ServerList::table('server_list')->find($server_id);
    if ($info)
    {
        if (!isset($user_id) || $user_id == 0)
        {
            return $player_name;
        }

        return dbConfig($info['real_server_id'],$info)
        ->table('player')
        ->where('actor_id', '=', $user_id)
        ->value('nickname');
    }
    return $player_name;
}

/**
 * 获取用户真实姓名
 * @param $user_id
 * @return string
 * @throws \think\Exception
 */
function get_user_real_name($user_id)
{
    $real_name = '';
    if (!isset($user_id))
        return $real_name;
    return Db::connect('db_config_main')->table('user_authentication')->where('user_id', '=', $user_id)->value('real_name');
}


/**
 * 获取玩家等级
 * @param $user_id
 * @param $server_id
 * @return string
 * @throws \think\Exception
 */
function get_player_level($user_id, $server_id)
{
    $player_info = "";
    if (!isset($user_id) || $user_id == 0)
    {
        return $player_info;
    }

    return dbConfigByReadBase($server_id)
        ->table('player')
        ->where('actor_id', '=', $user_id)
        ->value('level');
}

/**
 * 获取角色ID
 * @param $player_name 角色名称
 * @param $server_id   服务器ID
 * @return string
 * @throws \think\Exception
 */
function get_actor_id($player_name, $server_id)
{
    return dbConfigByReadBase($server_id)
        ->table('player')
        ->where('nickname', '=', $player_name)
        ->value('actor_id');
}


/**
 * 获取玩家最后登录时间
 * @param $user_id
 * @param $server_id
 * @return string
 * @throws \think\Exception
 */
function get_player_last_login_time($user_id, $server_id)
{
    $player_info = "";
    if (!isset($user_id) || $user_id == 0)
        return $player_info;

    return dbConfigByReadBase($server_id)
        ->table('player')
        ->where('actor_id', '=', $user_id)
        ->value('last_login_time');
}

/**
 * 获取玩家最后一次退出时间
 * @param $user_id
 * @param $server_id
 * @return string
 * @throws \think\Exception
 */
function get_player_last_logout_time($user_id, $server_id)
{
    $player_info = "";
    if (!isset($user_id) || $user_id == 0)
        return $player_info;

    return dbConfigByReadBase($server_id)
        ->table('player')
        ->where('actor_id', '=', $user_id)
        ->value('last_logout_time');
}


/**
 * 获取真实服务器ID
 *
 * @param $server_id
 * @return int|mixed
 */
function get_real_server_id($server_id)
{
    $real_server_id = 0;
    $info = \app\admin\model\ServerList::find($server_id);
    if ($info) {
        $real_server_id = $info['real_server_id'];
    }
    return $real_server_id;
}


/**
 * 获取用户角色数量
 * cq_game?.player
 * @param $server_id
 * @param $date
 * @return
 * @throws \think\Exception
 */
function get_player_role_count($server_id, $date)
{
    $real_server_id = get_real_server_id($server_id);
    if ($real_server_id > 0) {
        $where[] = ['1', '=', 1];
        if (isset($date) && !empty($date)) {
            $start_time = strtotime($date . ' 00:00:00');
            $end_time = strtotime($date . ' 23:59:59');
            $where[] = [
                ['create_time', '>=', $start_time],
                ['create_time', '<=', $end_time]
            ];
        }
        return dbConfigByReadBase($real_server_id)
            ->table('player')
            ->where($where)
            ->count('actor_id');
    }
}


/**
 * 统计开服前首日角色用户注册数
 * @param $server_id
 * @param $date
 * @return
 * @throws \think\Exception
 */
function get_player_role_count_by_first_day($server_id, $date)
{
    $real_server_id = get_real_server_id($server_id);
    if ($real_server_id > 0) {
        $where[] = ['create_server_id', '=', $server_id];
        if (isset($date) && !empty($date)) {
            $start_time = strtotime($date . ' 00:00:00');
            $end_time = strtotime($date . ' 23:59:59');
            $where[] = ['create_time', 'between', [$start_time, $end_time]];
        }
        return dbConfigByReadBase($real_server_id)->table('player')->where($where)->count('actor_id');
    }
}


/**
 * 统计开服前次日角色用户注册数
 * @param $server_id
 * @param $date
 * @return
 * @throws \think\Exception
 */
function get_player_role_count_by_second_day($server_id, $date)
{
    $real_server_id = get_real_server_id($server_id);
    if ($real_server_id > 0) {
        $where[] = ['create_server_id', '=', $server_id];
        if (isset($date) && !empty($date)) {
            //当前时间戳+1天 2017-01-10 21:04:11
            date('Y-m-d H:i:s', strtotime('+1day'));
            $start_time = strtotime("+1 day", strtotime($date . ' 00:00:00'));
            $end_time = strtotime("+1 day", strtotime($date . ' 23:59:59'));
            $where[] = ['create_time', 'between', [$start_time, $end_time]];
        }
        return dbConfigByReadBase($real_server_id)->table('player')->where($where)->count('actor_id');
    }
}


/**
 * 登录账号数
 * @param $server_id
 * @param $date
 * @return
 * @throws \think\Exception
 * @throws \think\db\exception\BindParamException
 * @throws \think\exception\PDOException
 */
function get_player_login_count($server_id, $date)
{
    $start_time = strtotime($date . ' 00:00:00');
    $end_time = strtotime($date . ' 23:59:59');
    $table_name = 'cq_log.Log' . date('Ymd', strtotime($date));

    $where[] = [
        ['serverId', '=', $server_id],
        ['userId', '>', 0],
        ['actionId', '=', 1]
    ];
    return Db::connect('db_config_log_read')
        ->table($table_name)
        ->distinct(true)
        ->field('userId')
        ->where($where)
        ->whereTime('logtime', 'between', [$start_time, $end_time])
        ->group('userId')
        ->count();
}


/**
 * 统计当前在线用户数
 * @param $server_id
 * @return
 * @throws \think\Exception
 */
function get_player_online_count($server_id)
{
    $table_name = 'cq_log.Log' . date('Ymd');
    $where[] = [
        ['serverId', '=', $server_id],
        ['moduleId', '=', 2]
    ];

    return Db::connect('db_config_log_read')
        ->table($table_name)
        ->where($where)
        ->order('logid desc')
        ->limit(1)
        ->value('value');
}

/**
 * 获取用户注册时间
 * @param $user_id
 * @return int|mixed
 */
function get_user_register_time($user_id)
{
    $user_register_time = 0;
    if ($user_id) {
        return \app\admin\model\UserInfo::where('UserID', '=', $user_id)->value('RegisterTime');
    }
    return $user_register_time;
}

/**
 * 获取用户账户名称
 * 读取数据表cq_game?->player
 * @param $user_id
 * @param $server_id
 * @return mixed|string
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_user_account($user_id, $server_id)
{
    $user_account = "";
    if (!isset($user_id) || $user_id == 0)
        return $user_account;

    $account_id = dbConfig($server_id)->table('player')->where('actor_id', '=', $user_id)->value('account_id');
    if ($account_id) {
        $info = \app\admin\model\UserInfo::where('UserID', '=', $account_id)->find();
        if ($info) {
            if ($info['Phone_UserName']) {
                $user_account = $info['Phone_UserName'];
            } else {
                $user_account = $info['UserName'];
            }
        }
    }
    return $user_account;
}


/**
 * 贵族等级
 * @param $user_id
 * @param $server_id
 * @return int
 * @throws \think\Exception
 */
function get_special_level($user_id, $server_id)
{
    $speial_level = 0;
    if (!isset($user_id) || $user_id == 0)
        return $speial_level;

    return dbConfig($server_id)->table('vip_data')->where('actor_id', '=', $user_id)->value('vip_level');
}

/**
 * 获取服务器名称
 * @param $server_id  服务器ID
 * @return
 */
function get_server_name($server_id)
{
    return \app\admin\model\ServerList::table('server_list')->where('id', $server_id)->value('servername');
}

/**
 * 获取服务器区ID+服务器名称
 * @param $server_id
 * @return string
 */
function get_area_server_name($server_id)
{
    $area_server_name = '';
    $info = \app\admin\model\ServerList::table('server_list')->field('area_id,servername')->find($server_id);
    if ($info) {
        $area_server_name = $info['area_id'] . ' 区 - ' . $info['servername'];
    }
    return $area_server_name;
}


/**
 * 获取道具名称
 * @param $prop_id
 * @return mixed
 */
function get_prop_name($prop_id)
{
    if (!\app\admin\model\PropCsv::where('type_id', $prop_id)->find()) {
        \think\facade\Log::write("获取道具名称异常【type_id】:" . $prop_id);
    }
    return \app\admin\model\PropCsv::where('type_id', $prop_id)->value('type_name');
}

/**
 * 获取游戏任务名称
 * @param $quest_id
 * @return mixed
 */
function get_quest_name($quest_id)
{
    return \app\admin\model\GameQuestData::where('id', $quest_id)->value('name');
}

/**
 * 获取多个道具列表名称与数量
 * @param $prop_list
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_prop_list_name($prop_list)
{
    $prop_list_desc = '';
    if (isset($prop_list)) {
        $propArr = explode(';', $prop_list);
        foreach ($propArr as $key => $value) {
            if (isset($value)) {
                $itemArr = explode('|', $value);
                if (!\app\admin\model\PropCsv::where('type_id', $itemArr[0])->find()) {
                    \think\facade\Log::write("获取道具名称异常【type_id】:" . $itemArr[0]);
                }
                $ret_desc = \app\admin\model\PropCsv::where('type_id', $itemArr[0])->value('type_name');
                $prop_list_desc .= $ret_desc . '|' . $itemArr[1] . ';';
            }
        }
    }
    return $prop_list_desc;
}

/**
 * 获取每天小时数
 **/
function get_day_hour()
{
    $arr_hour = array();
    for ($i = 0; $i < 24; $i++) {
        $arr_hour[$i] = $i;
    }
}

/**
 * 获取游戏日志类型名称（描述）
 * @param $module_id
 * @return mixed
 */
function get_game_action_name($module_id)
{
    return \app\Admin\model\GameLogAction::name('game_log_action')->where('action_value', '=', $module_id)->value('action_desc');
}

/**
 * 校验邮箱格式
 * @param $email
 * @return mixed
 */
function check_email($email)
{
    $preg = "/^\w+([-_.]\w+)*@\w+([-_.]\w+)*(\.\w+){0,3}$/i";
    preg_match($preg, $email, $res);
    return $res;
}

/**
 * 获取福利礼包名称
 * @param $id
 * @return mixed
 */
function get_welfare_title($id)
{
    return \app\admin\model\Welfare::where('id', $id)->value('title');
}

/**
 * 获取活动分类名称
 * @param $id
 * @return mixed
 */
function get_activity_type_name($id)
{
    return \app\admin\model\ActivityType::where('id', $id)->value('name');
}


/**
 * 获取任务名称
 * @param $id
 * @return mixed
 */
function get_task_name($id)
{
    return \app\admin\model\Task::where('id', $id)->value('name');
}


/**
 * 获取活动目标名称
 * @param $id
 * @return mixed
 */
function get_target_name($id)
{
    return \app\admin\model\Target::where('id', $id)->value('name');
}


/**
 * 获取服务器所在服务器HostName
 * @param $server_id
 * @return mixed
 */
function get_server_hostname($server_id)
{
    return \app\admin\model\ServerConfig::where('id', $server_id)->value('hostname');
}

/**
 * 获取开服时间
 * @param $server_id
 * @return mixed
 *
 */
function get_open_server_time($server_id)
{
    return \app\admin\model\ServerList::where('id', $server_id)->value('open_time');;
}

/**
 * 根据角色ID获取当前角色用户总充值
 * @param $user_id
 */
function get_total_recharge_by_user_id($user_id)
{
    $where[] = [
        ['user_id', '=', $user_id],
        ['status', '=', 1]
    ];
    return \app\admin\model\RechargeData::where($where)->sum('money');
}

/**
 * 获取首次充值时间
 * @param $user_id
 * @return mixed
 */
function get_first_recharge_time($user_id)
{
    return \app\admin\model\RechargeData::where('user_id', $user_id)->order('add_time asc')->limit(1)->value('add_time');
}

/**
 * 获取最近一次充值时间
 * @param $user_id
 * @return mixed
 */
function get_last_recharge_time($user_id)
{
    return \app\admin\model\RechargeData::where('user_id', $user_id)->order('add_time desc')->limit(1)->value('add_time');
}

/**
 * 充值用户数量
 * @param $server_id
 * @param $date
 * @return float|int|string
 */
function get_recharge_user_count($server_id, $date)
{
    $start_date = $date . ' 00:00:00';
    $end_date = $date . ' 23:59:59';

    return \app\admin\model\RechargeData::where('server_id', '=', $server_id)
        ->whereTime('add_time', 'between', [$start_date, $end_date])
        ->count();

}

/**
 * 获取首日充值人数
 * @param $server_id
 * @param $date
 * @return float|int|string
 */
function get_first_recharge_count($server_id, $date)
{
    $start_time = $date . ' 00:00:00';
    $end_time = $date . ' 23:59:59';
    $where[] = [
        ['server_id', '=', $server_id],
//        ['add_time', 'between', [$start_time, $end_time]]
    ];

    $count = \app\admin\model\RechargeData::where($where)->whereTime('add_time', 'between', [$start_time, $end_time])->distinct(true)->field('user_id')->count();
    return $count ? $count : 0;

}

/**
 * 分区服，查询真实服充值用户人数
 * @param $server_id
 * @param $date
 * @return float|int|string
 */
function real_server_recharge_user($server_id, $date)
{
    /**
     * SELECT
     * r.money,
     * r.user_id
     * FROM
     * recharge_data r
     * LEFT JOIN cq_game1.player p ON r.user_id = p.actor_id
     * WHERE
     * p.create_server_id = 1
     * AND r.add_time BETWEEN '2021-04-29 00:00:00'
     * AND '2021-04-29 23:59:59';
     **/

    $start_date = $date . ' 00:00:00';
    $end_date = $date . ' 23:59:59';
    $where[] = [
        ['p.create_server_id', '=', $server_id],
        ['r.add_time', 'between', [$start_date, $end_date]]
    ];
    $count = \app\admin\model\RechargeData::alias('r')
        ->join('cq_game' . $server_id . '.player p', 'p.actor_id=r.user_id', 'LEFT')
        ->where($where)
        ->group('r.user_id')
        ->count();
    return $count ? $count : 0;
}

/**
 * 查询充值用户人数（包括合服与未合服的服务器充值数据）
 * @param $server_id
 * @param $date
 */
function server_recharge_user($server_id, $date)
{
    $start_date = $date . ' 00:00:00';
    $end_date = $date . ' 23:59:59';
    $where[] = [
        ['server_id', '=', $server_id],
        ['add_time', 'between', [$start_date, $end_date]]
    ];
    $count = \app\admin\model\RechargeData::where($where)->count();
    return $count ? $count : 0;
}

/**
 * 分区服，查询真实服充值总金额
 * @param $server_id
 * @param $date
 * @return float|int
 */
function real_server_recharge_total($server_id, $date)
{
    $start_date = $date . ' 00:00:00';
    $end_date = $date . ' 23:59:59';
    $total = \app\admin\model\RechargeData::alias('r')
        ->join('cq_game' . $server_id . '.player p', 'p.actor_id=r.user_id', 'LEFT')
        ->whereTime('r.add_time', 'between', [$start_date, $end_date])
        ->where('p.create_server_id=' . $server_id)
        ->sum('r.money');
    return $total ? $total : 0;
}

/**
 * 分区服，查询真实服注册玩家数
 * @param $server_id
 * @param $date
 * @return int
 * @throws \think\Exception
 */
function real_server_register_user($server_id, $date)
{
    $start_date = strtotime($date . ' 00:00:00');
    $end_date = strtotime($date . ' 23:59:59');
    $count = dbConfigByReadBase($server_id)->table('player')
        ->whereTime('create_time', 'between', [$start_date, $end_date])
        ->where('create_server_id=' . $server_id)
        ->count();
    return $count ? $count : 0;
}

/**
 * 获取渠道名称
 * @param $id
 * @return array|PDOStatement|string|\think\Model|null
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_channel_name($id)
{
    return \app\admin\model\Channel::where('id', '=', $id)->value('channel_name');
}

/**
 * 获取礼包类型名称（gift_data_info->gift_name）
 * @param $id
 * @return mixed
 *
 */
function get_gift_type_name($id)
{
    return \app\admin\model\GiftDataInfo::where('id', '=', $id)->value('gift_name');
}


/**
 * 通过skill_id与获取技能名称
 * @param $skill_id  技能ID
 * @return array
 */
function get_skill_by_csv($skill_id)
{
    $skill_name = '';
    switch ($skill_id) {
        case 100:
        case 200:
        case 300:
            $skill_name = '普通攻击';
            break;
        case 101:
            $skill_name = '天罡战气';
            break;
        case 102:
            $skill_name = '攻杀剑术';
            break;
        case 103:
            $skill_name = '刺杀剑术';
            break;
        case 104:
            $skill_name = '断岳扫';
            break;
        case 105:
            $skill_name = '烈火剑术';
            break;
        case 106:
            $skill_name = '无畏冲锋';
            break;
        case 107:
            $skill_name = '长虹贯日';
            break;
        case 108:
            $skill_name = '移形幻影';
            break;
        case 201:
            $skill_name = '天雷诀';
            break;
        case 202:
            $skill_name = '元素盾';
            break;
        case 203:
            $skill_name = '控火术';
            break;
        case 204:
            $skill_name = '驱雷术/游龙诀';
            break;
        case 205:
            $skill_name = '冰风暴';
            break;
        case 206:
            $skill_name = '元素光环';
            break;
        case 207:
            $skill_name = '流星火雨';
            break;
        case 208:
            $skill_name = '斗转星移';
            break;
        case 301:
        case 308:
            $skill_name = '玄宗符术';
            break;
        case 302:
            $skill_name = '施毒术';
            break;
        case 303:
            $skill_name = '神圣护甲';
            break;
        case 304:
            $skill_name = '回春术';
            break;
        case 305:
            $skill_name = '御兽术';
            break;
        case 306:
            $skill_name = '玄影盾';
            break;
        case 307:
            $skill_name = '大衍咒术';
            break;
        case 309:
            $skill_name = '神兽合体';
            break;
        case 711:
            $skill_name = '十方封魔';
            break;
        case 712:
        case 721:
            $skill_name = '五芒镇邪';
            break;
        case 713:
        case 731:
            $skill_name = '末日审判';
            break;
        case 722:
            $skill_name = '断空噬地';
            break;
        case 723:
        case 732:
            $skill_name = '修罗绝杀';
            break;
        case 733:
            $skill_name = '天魔往生';
            break;
        case 318:
            $skill_name = '唤灵术';
            break;
        case 802:
            $skill_name = '近身普通攻击';
            break;
        case 803:
            $skill_name = '远程普通攻击';
            break;
        case 804:
            $skill_name = '乱舞九天';
            break;
        case 805:
            $skill_name = '紫气东来';
            break;
        case 806:
            $skill_name = '寒冰领域';
            break;
        case 807:
            $skill_name = '冰晶爆轰';
            break;
        case 808:
            $skill_name = '爆炎';
            break;
        case 809:
            $skill_name = '炎龙无双';
            break;
        case 810:
            $skill_name = '狂雷天牢';
            break;
        case 811:
            $skill_name = '千里冰封';
            break;
        case 900:
            $skill_name = '天启';
            break;
        default:
            $skill_name = '';
            break;
    }

    return $skill_name;

}

/**
 * 获取时装名称
 * @param $appearance_id
 */
function get_appearance_name($appearance_id)
{
    switch ($appearance_id) {
        case 1001:
            $appearance_name = "盟主时装";
            break;
        case 1002:
        case 1003:
        case 1004:
            $appearance_name = "信仰之跃";
            break;
        case 52 :
        case 53 :
        case 54 :
            $appearance_name = "阳光沙滩";
            break;
        case 56:
            $appearance_name = "熊猫人";
            break;
        case 413:
            $appearance_name = "未来战士";
            break;
        default:
            $appearance_name = "";
            break;
    }
    return $appearance_name;
}

/**
 * 获取神炉名称
 * @param $furnace_type
 * @return string
 */
function get_furnace_name($furnace_type)
{
    switch ($furnace_type) {
        case 0:
            $furnace_name = "角色-青龙";
            break;
        case 1:
            $furnace_name = "角色-白虎";
            break;
        case 2:
            $furnace_name = "角色-朱雀";
            break;
        case 3:
            $furnace_name = "角色-玄武";
            break;
        case 4:
            $furnace_name = "角色-武境";
            break;
        case 5:
            $furnace_name = "角色-头衔";
            break;
        case 6:
            $furnace_name = "角色-神翼";
            break;
        case 7:
            $furnace_name = "角色-杀戮";
            break;
        case 8:
            $furnace_name = "内功";
            break;
        case 9:
            $furnace_name = "境界";
            break;
        case 10:
            $furnace_name = "角色-重生";
            break;
        case 11:
            $furnace_name = "分身-青龙";
            break;
        case 12:
            $furnace_name = "分身-白虎/战鼓";
            break;
        case 13:
            $furnace_name = "分身-朱雀/徽章";
            break;
        case 14:
            $furnace_name = "分身-玄武/魂盘";
            break;
        case 15:
            $furnace_name = "分身-神翼";
            break;
        case 16:
            $furnace_name = "分身-筋脉";
            break;
        case 17:
            $furnace_name = "分身-重生";
            break;
        default:
            $furnace_name = "";
            break;
    }
    return $furnace_name;
}


/**
 * 活跃ARPU
 * @param $server_id
 * @param $log_time
 * @param $recharge
 */
function arpu($server_id, $log_time, $recharge)
{
    $arpu = 0.00;
    $user_count = get_recharge_user_count($server_id, date('Y-m-d', strtotime($log_time)));
    if ($user_count == 0)
        return $arpu;

    $log_count = get_player_login_count($server_id, date('Y-m-d', strtotime($log_time)));
    if ($log_count == 0)
        return $arpu;

    $arpu = sprintf("%1\$.2f", $recharge / $log_count / 100);
    return $arpu;

}

/**
 * 付费ARPU
 * @param $server_id
 * @param $log_time
 * @param $recharge
 * @return float|string
 */
function pay_arpu($server_id, $log_time, $recharge)
{
    $arpu = 0.00;
    $user_count = get_recharge_user_count($server_id, date('Y-m-d', strtotime($log_time)));
    if ($user_count == 0)
        return $arpu;

    $arpu = sprintf("%1\$.2f", ($recharge / $user_count) / 100);
    return $arpu;
}

/**
 * 付费率
 * @param $server_id
 * @param $log_time
 * @return string
 * @throws \think\Exception
 * @throws \think\db\exception\BindParamException
 * @throws \think\exception\PDOException
 */
function pay_rate($server_id, $log_time)
{
    $pay_rate = '0.00';
    $login_count = get_player_login_count($server_id, date('Y-m-d', strtotime($log_time)));
    if ($login_count == 0)
        return $pay_rate;
    $user_count = get_recharge_user_count($server_id, date('Y-m-d', strtotime($log_time)));
    $pay_rate = sprintf("%1\$.2f", ($user_count / $login_count) * 100) . '%';
    return $pay_rate;
}

/**
 * 付费留存
 * @param $recharge
 * @param $server_id
 * @param $date
 * @return float|string
 */
function pay_retained($recharge, $server_id, $date)
{
    $pay_retained = 0.00;
    if ($recharge == 0)
        return $pay_retained;

    $recharge_count = get_first_recharge_count($server_id, $date);
    if ($recharge_count == 0)
        return $pay_retained;

    $pay_retained = round(($recharge / $recharge_count) * 100, 2) . '%';
    return $pay_retained;
}

/**
 * 每日LTV
 * @param $recharge
 * @param $role_count
 * @return float
 */
function ltv($recharge, $role_count)
{
    $ret_ltv = 0.00;
    if ($role_count == 0)
        return $ret_ltv;

    return round($recharge / $role_count, 2);
}


/**
 * 商店类型
 * @param $shop_id
 * @return string
 */
function get_shop_type($shop_id)
{
    switch ($shop_id) {
        case 1:
            $shop_type_name = "热销商城";
            break;
        case 2:
            $shop_type_name = "元宝商城";
            break;
        case 3:
            $shop_type_name = "技能商城";
            break;
        case 4:
            $shop_type_name = "银票商城";
            break;
        case 5:
            $shop_type_name = "限购商城";
            break;
        case 6:
            $shop_type_name = "开服礼包特惠";
            break;
        case 7:
            $shop_type_name = "开服限购礼包";
            break;
        case 8:
            $shop_type_name = "开服分身重生礼包";
            break;
        case 9:
            $shop_type_name = "开服圣装礼包";
            break;
        case 10:
            $shop_type_name = "开服金币礼包";
            break;
        case 11:
            $shop_type_name = "开服神翼礼包";
            break;
        case 12:
            $shop_type_name = "开服寻宝礼包";
            break;
        case 13:
            $shop_type_name = "开服重生礼包";
            break;
        case 14:
            $shop_type_name = "刺客时装";
            break;
        case 15:
            $shop_type_name = "合服特惠礼包";
            break;
        case 16:
            $shop_type_name = "合服商城";
            break;
        case 17:
            $shop_type_name = "节日特惠礼包-1";
            break;
        case 18:
            $shop_type_name = "节日时装";
            break;
        case 19:
            $shop_type_name = "寻宝助力礼包";
            break;
        case 20:
            $shop_type_name = "一折礼包";
            break;
        case 21:
        case 51:
            $shop_type_name = "跨服商城";
            break;
        case 22:
            $shop_type_name = "限时黑市";
            break;
        case 23:
            $shop_type_name = "节日寻宝助力礼包";
            break;
        case 24:
            $shop_type_name = "节日特惠礼包-2";
            break;
        case 25:
            $shop_type_name = "节日特惠礼包-3";
            break;
        case 26:
            $shop_type_name = "节日特惠礼包-4";
            break;
        case 27:
            $shop_type_name = "节日特惠礼包-5";
            break;
        case 28:
            $shop_type_name = "节日特惠礼包-6";
            break;
        case 29:
            $shop_type_name = "节日特惠礼包-7";
            break;
        case 30:
            $shop_type_name = "节日特惠礼包-8";
            break;
        case 31:
            $shop_type_name = "节日时装-2";
            break;
        case 32:
            $shop_type_name = "节日时装-3";
            break;
        default:
            $shop_type_name = "";
            break;
    }
    return $shop_type_name;
}

/**
 * 货币类型
 * @param $currency_id
 * @return string
 */
function get_currency_type($currency_id)
{
    switch ($currency_id) {
        case 101:
            $currency_name = "金币";
            break;
        case 102:
            $currency_name = "银票";
            break;
        case 103 :
            $currency_name = "元宝";
            break;
        case 104:
            $currency_name = "荣耀积分";
            break;
        default:
            $currency_name = "";
            break;
    }
    return $currency_name;
}

/**
 * 获取管理员关联的服务器列表
 * @param $uid
 * @return array|PDOStatement|string|\think\Collection|\think\model\Collection
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function get_user_server_list($uid)
{
    $where[] = ['uid', '=', $uid];
    $ret_list = \app\admin\model\UserServer::where($where)->field('server_id')->select();
    return $ret_list->toArray();
}

/**
 * 根据管理员ID获取关联服务器信息
 * @param $uid
 * @return mixed
 */
function get_user_server_by_id($uid)
{
    $where[] = ['uid', '=', $uid];
    return \app\admin\model\UserServer::where($where)->limit(1)->order('server_id desc')->value('server_id');
}

/**
 * 获取游戏界面名称
 * @param $surface_type
 * @param $surface_id
 * @param $level
 * @return mixed
 */
function get_surface_type_name($surface_type, $surface_id, $level)
{
    $where[] = [
        ['surface_id', '=', $surface_id],
        ['surface_type', '=', $surface_type],
        ['level', '=', $level],
    ];
    return \app\admin\model\GameSurface::where($where)->value('symbol_chs');
}

/**
 * 判断数据库是否存在
**/
function show_drop_database_button($id)
{
    $dbname = 'cq_game' . $id;
    $conn = mysqli_connect(config('admin.DB_HOST'),  config('admin.DB_USER'), config('admin.DB_PASS'));
    $result = mysqli_query($conn, 'show databases;');
    $data = array();//用来存在数据库名
    mysqli_data_seek($result, 0);
    while ($dbdata = mysqli_fetch_array($result))
    {
        $data[] = $dbdata['Database'];
    }
    mysqli_data_seek($result, 0);
    if (in_array($dbname, $data))
    {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取用户封停状态
**/
function get_user_ban_status($user_id)
{
    return Db::connect('db_config_main_read')->table('user_info')->where('UserID', '=', $user_id)->value('BanFlag');
}
