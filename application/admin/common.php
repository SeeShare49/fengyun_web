<?php

//模块函数文件
use app\admin\model\ServerList;
use app\admin\model\UserInfo as UserInfoModel;
use app\common\ServerManage;
use think\Db;
use think\facade\Log;

/**
 * 获取配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group = 0)
{
    $list = config('CONFIG_GROUP_LIST');
    return $group ? (isset($list[$group]) ? $list[$group] : '') : '';
}

/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type = 0)
{
    $list = config('CONFIG_TYPE_LIST');
    return $list[$type];
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string)
{
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}


/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的表名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @param string $remark
 * @return boolean
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null, $remark = '')
{
    //参数检查
    if (empty($action) || empty($model)) {
        return '参数不能为空';
    }

    //查询行为,判断是否执行
    $action_info = db('action')->where('name', $action)->find();

    if ($action_info['status'] != 1) {
        return '该行为被禁用或删除';
    }

    if (!is_array($record_id)) {
        $record_id = [$record_id];
    }

    foreach ($record_id as $item) {
        //插入行为日志
        $data['action_id'] = $action_info['id'];
        $data['user_id'] = $user_id;
        $data['action_ip'] = request()->ip();
        $data['model'] = $model;
        $data['record_id'] = $item;
        $data['create_time'] = time();

        if ($remark) {
            $data['remark'] = $remark;
        }
        \app\admin\model\ActionLog::insert($data);
    }
}

/**
 * 系统（运行平台）充值记录信息
 * @param null $server_id 服务器ID
 * @param null $player_name 玩家昵称
 * @param null $ingot 元宝数量
 * @param null $gold 金币数量
 * @param null $silver 银票数量
 */
function sys_invest_record($server_id = null, $player_name = null, $ingot = null, $gold = null, $silver = null)
{
    $data['server_id'] = $server_id;
    $data['player_name'] = $player_name;
    $data['ingot'] = $ingot;
    $data['gold'] = $gold;
    $data['silver'] = $silver;
    $data['ip'] = request()->ip();
    $data['admin_id'] = UID;// session('uid');//当前管理员用户ID
    $data['create_time'] = time();
    \app\admin\model\SysInvest::insert($data);
}

/**
 * 系统（运营后台）添加道具记录信息
 * @param $server_id    服务器ID
 * @param $player_name  玩家昵称
 * @param $prop_id      道具ID
 * @param $prop_num     道具数量
 */
function sys_prop_record($server_id, $player_name, $prop_id, $prop_num)
{
    $data['server_id'] = $server_id;
    $data['player_name'] = $player_name;
    $data['prop_id'] = $prop_id;
    $data['prop_num'] = $prop_num;
    $data['ip'] = request()->ip();
    $data['admin_id'] = UID;// session('uid');
    $data['create_time'] = time();
    \app\admin\model\SysProp::insert($data);
}

/**
 * 添加退款记录
 * @param $server_id    服务器ID
 * @param $user_id      用户ID（角色ID）
 * @param $order_id     订单编号
 * @param $trade_no     Apple Pay 交易号
 * @param $amount       订单金额
 */
function refund_record($server_id = null, $user_id = null, $order_id = null, $trade_no = null, $money = null, $amount = null)
{
    $data['server_id'] = $server_id;
    $data['user_id'] = $user_id;
    $data['order_id'] = $order_id;
    $data['trade_no'] = $trade_no;
    $data['amount'] = $amount;
    $data['money'] = $money;
    $data['status'] = 0; //默认未处理
    $data['create_time'] = time();
    return \app\admin\model\RefundData::insertGetId($data);
}


/**
 * 数据列表转换为数据树
 * @param $lists
 * @param int $pid
 * @param int $level
 * @return array
 */
function list_to_tree($lists, $pid = 0, $level = 0)
{
    $treeList = [];
    foreach ($lists as $item) {
        if ($item['pid'] == $pid) {
            $item['level'] = $level;
            $childItem = list_to_tree($lists, $item['id'], $level + 1);
            if ($childItem) {
                $item['child'] = $childItem;
            }
            array_push($treeList, $item);
        }
    }
    return $treeList;
}


/**
 * 获取系统版本信息
 */
function get_hula_version()
{
    //$update_path = __ROOT__ . 'application/update/';
    $update_path = './application/update/';
    //系统版本号存放文件地址
    $filename = $update_path . "info";

    if (!file_exists($filename)) {
        return false;
    }

    $handle = fopen($filename, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
    //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
    $version_info = fread($handle, filesize($filename));
    fclose($handle);

    if (!$version_info) {
        return false;
    }

    return json_decode($version_info);
}


/**
 * 格式化字节大小
 * @param number $size 字节数
 * @param string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}


/**
 * 系统非常规MD5加密方法
 * @param string $str 要加密的字符串
 * @param string $key
 * @return string
 */
function yw_ucenter_md5($str, $key = 'YwUserKey')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 获取聊天消息并校验消息内容是否存在过滤内容
 * 过滤字库分为预警屏蔽库和禁言屏蔽库
 * 1、预警屏蔽库 对用户发送的消息内容进行提示
 * 2、禁言屏蔽库 直接对用户禁言
 * @param $player_id
 * @param $str
 * @return false|string
 */
function get_self_str($str = null)
{
    $info = \app\admin\model\FilterControl::find(1);
    if ($info) {
        $filter_arr = explode('|', $info['filter_str']);
        // $retStr = '' === $str ? '' : substr($str, 32, -3);
        if (!empty($str)) {
            $str = extract_chat_content($str);
            //{"1":{"nShowType":1,"sContent":"我们一起"}}
            foreach ($filter_arr as $value) {
                if (strpos($str, $value) !== false) {
                    //{:str_replace($filter_str,"<font color=#ff4500><b>$filter_str</b></font>",$vo.filter_str)}
                    $str = str_replace($value, "<font color=	#FFA500><b>$value</b></font>", $str);
                }
            }
        }
        return $str;
    }
}


/**
 * 提取玩家聊天内容
 * @param $chat_content
 */
function extract_chat_content($chat_content)
{
    $ret_content = '';
    $content = json_decode($chat_content, true);
    if (!empty($content)) {
        if (is_array($content)) {
            $sContent_count = count($content);
            for ($i = 1; $i <= $sContent_count; $i++) {
                if (isset($content[$i]['sContent'])) {
                    $ret_content .= $content[$i]['sContent'] . ' ';
                }
            }
        }
    }
    return $ret_content;
}

/**
 * 过滤用户发送的聊天消息，发送对用户（永久）禁言操作
 * @param $str
 */
function disable_user_filter_str($str)
{
    $info = \app\admin\model\FilterControl::find(2);
    $filter_arr = explode('|', $info['filter_str']);
    if (!empty($str)) {
        foreach ($filter_arr as $value) {
            if (strpos($str, $value) !== false) {
                return $value;
            }
        }
    }
    return "";
}


/**
 * 获取管理员昵称
 * @param $id
 * @return mixed|string
 */
function get_admin_user_name($id)
{
    $nickname = '';
    $info = \app\admin\model\Users::find($id);
    if ($info) {
        $nickname = $info['nickname'];
    }
    return $nickname;
}

function get_admin_nick_name($user_name)
{
    $nickname = '';
    $info = \app\admin\model\Users::where('username', '=', trim($user_name))->find();
    if ($info) {
        $nickname = $info['nickname'];
    }
    return $nickname;
}

/**
 * 校验数据库数据表是否存在
 * @param $connect_str
 * @param $table_name
 * @return
 * @throws \think\Exception
 */
function check_exists_table($connect_str, $table_name)
{
    return Db::connect($connect_str)->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
}


/**
 * 根据条件删除聊天日志信息
 * @param $connect_str 链接库
 * @param $table_prefix 表前缀
 * @param $user_id
 * @throws \think\db\exception\BindParamException
 * @throws \think\exception\PDOException
 */
function clear_chat_log($connect_str,$table_prefix, $user_id)
{
    if (isset($user_id)) {
        $server_list = ServerManage::getServerList();
        $server_list_count = count($server_list);
        for ($i = 0; $i < $server_list_count; $i++) {
            $server_id = $server_list[$i]['id'];
            $serverInfo = ServerList::find($server_id);
            if ($serverInfo) {
                $table = $table_prefix . $serverInfo['real_server_id'];

                $lists_sql = "select actor_id from {$table}.player where account_id={$user_id}";

                $Model = new UserInfoModel();
                $ids = '';
                $info = $Model->query($lists_sql);
                $info_count = count($info);
                if ($info_count > 0) {
                    for ($j = 0; $j < $info_count; $j++) {
                        $ids .= $info[$j]['actor_id'] . ',';
                    }
                    $ids = trim($ids, ',');
                    $curr_date = date("Ymd");

                    //清理15日内的被封号的用户聊天消息
                    for ($i = 1; $i <= 15; $i++) {
                        $table_name ='chat_log' . date("Ymd", strtotime("-$i day", strtotime($curr_date)));
                        //容错，判断消息数据表是否存在 TODO：
                        if(check_exists_table($connect_str,$table_name) && isset($ids)){
                            $del_log_sql = "delete from {$table_name} where actor_id in ({$ids})";
                            Log::write("删除聊天记录执行sql:" . $del_log_sql);
                            $ret = Db::connect('db_chat_log')->execute($del_log_sql);
                            if ($ret) {
                                Log::write("封号用户UserID:【{$user_id}】聊天记录删除成功!");
                            }
                        }
                    }
                }
            }
        }
    }
}





