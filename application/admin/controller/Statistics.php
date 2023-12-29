<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\common\GameLogActionManage;
use app\common\ServerManage;
use page\Page;
use think\Db;
use think\Exception;
use think\facade\Debug;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

define('GROUP_ID', config('admin.GROUP_ID'));

class Statistics extends Base
{
    /**
     * 综合统计(备用)
     */
    public function comprehensive()
    {
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
        $is_guild = false;
        $ids = '';
        $temp_server_ids = '';
        if (GROUPID == GROUP_ID) {
            $is_guild = true;
            $s_ids = get_user_server_list(UID);
            $temp_server_ids = '';
            foreach ($s_ids as $key => $value) {
                $temp_server_ids .= $value['server_id'] . ',';
            }
            $ids = explode(',', rtrim($temp_server_ids, ","));
            $server_list = ServerManage::getServerListByIds($ids);
        } else {
            $server_list = ServerManage::getServerList();
        }


        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }
        $tablename = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");
       Log::write("table name:".$tablename);
        $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $tablename . "'");
        if (!$exists_table) {
            $this->error("数据表【{$tablename}】不存在！！！！");
        }
        $filed = "logid,serverId,serverName,moduleName,logtime, 
        sum(case when moduleId=2 then value else 0 end ) as value,
        max(case when moduleId=2 then value else 0 end) as max_value,
        sum(case when (moduleId=1 or moduleId=3) and actionId=8 then value else 0 end) as recharge";

        $where[] = ['logtime', 'between', [$start, $end]];

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            if ($is_guild) {
                $server_ids = $ids;
            } else {
                $server_ids = explode(',', $server_id);
            }
        }

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['serverId', 'between', [$start_server_id, $end_server_id]];
            $search = true;
        }
        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            if ($is_guild) {
                $where[] = ['serverId', 'in', rtrim($temp_server_ids, ",")];
            } else {
                $where[] = ['serverId', 'in', $server_ids];
            }
        }

        if (empty($server_ids) && $is_guild == true) {
            $where[] = ['serverId', 'in', rtrim($temp_server_ids, ",")];
        }

        $lists = Db::connect('db_config_log_read')
            ->table($tablename)
            ->field($filed)
            ->where($where)
            ->order('serverId desc,logtime desc')
            ->group('serverId')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();


        //总登录账号数
        $total_login_count = 0;
        //总创建角色数
        $total_role_count = 0;
        //总充值人数
        $total_recharge_count = 0;
        //总充值金额
        $total_recharge_amount = 0;
        //总日充值笔数
        $total_day_recharge_count = 0;
        $list_count = count($lists);
        for ($i = 0; $i < $list_count; $i++) {
            $s_id = $lists[$i]['serverId'];
            $l_time = date('Y-m-d', strtotime($lists[$i]['logtime']));
            $total_login_count += get_player_login_count($s_id, $l_time);
            $total_role_count += get_player_role_count($s_id, $l_time);
            $total_recharge_count += get_recharge_user_count($s_id, $l_time);
            $total_recharge_amount += $lists[$i]['recharge'];
            $total_day_recharge_count += server_recharge_user($s_id, $l_time);
        }

        $this->assign([
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'server_list' => $server_list,
            'server_id' => $server_ids,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'page' => $page,
            'date' => $date,
            'total_login_count' => $total_login_count,
            'total_role_count' => $total_role_count,
            'total_recharge_count' => $total_recharge_count,
            'total_recharge_amount' => $total_recharge_amount,
            'total_day_recharge_count' => $total_day_recharge_count,
            'meta_title' => '综合统计'
        ]);
        return $this->fetch();
    }

    /**
     * 综合统计
     **/
    public function index()
    {
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
        $server_list = ServerManage::getServerList();
        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }
        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");

        $filed = "logid,serverId,serverName,moduleName,logtime, 
        sum(case when moduleId=2 then value else 0 end ) as value,
        max(case when moduleId=2 then value else 0 end) as max_value,
        sum(case when (moduleId=1 or moduleId=3) and actionId=8 then value else 0 end) as recharge";

        $where[] = ['logtime', 'between', [$start, $end]];
        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
        }

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['serverId', 'between', [$start_server_id, $end_server_id]];
            $search = true;
        }
        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            $where[] = ['serverId', 'in', $server_ids];
        }
        /** serverId大于等于10000所属跨服不计入 **/
        $where[] = ['serverId', '<', 10000];
        $lists = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field($filed)
            ->where($where)
            ->order('serverId desc,logtime desc')
            ->group('serverId')
            ->select();
        $curr_page = input('page/d', 1);
        //总登录账号数
        $total_login_count = 0;
        //总创建角色数
        $total_role_count = 0;
        //总充值人数
        $total_recharge_count = 0;
        //总充值金额
        $total_recharge_amount = 0;
        //总日充值笔数
        $total_day_recharge_count = 0;
        $list_count = count($lists);
        $statistics_list = array();
        for ($i = 0; $i < $list_count; $i++) {
            $s_id = $lists[$i]['serverId'];
            $l_time_ymd = $lists[$i]['logtime'];
            $l_time = date('Y-m-d', strtotime($l_time_ymd));
            $recharge = $lists[$i]['recharge'];
            $player_login_count = get_player_login_count($s_id, $l_time);
            $player_role_count = get_player_role_count($s_id, $l_time);
            $get_recharge_user_count = get_recharge_user_count($s_id, $l_time);
            $max_value = $lists[$i]['max_value'];

            $total_login_count += $player_login_count;
            $total_role_count += $player_role_count;
            $total_recharge_count += $get_recharge_user_count;
            $total_recharge_amount += $recharge;
            $total_day_recharge_count += server_recharge_user($s_id, $l_time);

            //初始化综合统计实体类
            $data = new comprehensive();
            $data->logtime = $l_time;
            $data->server_id = $s_id;
            $data->opentime = date('Y-m-d H:i:s', get_open_server_time($s_id));
            $data->server_name = get_area_server_name($s_id);
            $data->login_count = $player_login_count;
            $data->role_count = $player_role_count;
            $data->recharge_count = $get_recharge_user_count;
            $data->recharge_amount = sprintf("%1\$.2f", $recharge / 100);
            $data->daily_activity = $player_login_count;
            $data->daily_recharge_count = server_recharge_user($s_id, $l_time);
            $data->activity_arpu = arpu($s_id, $l_time_ymd, $recharge);
            $data->pay_arpu = pay_arpu($s_id, $l_time_ymd, $recharge);
            $data->pay_rate = pay_rate($s_id, $l_time_ymd);
            $data->current_online = get_player_online_count($s_id);
            $data->max_online = $max_value;

            array_push($statistics_list, objectToArray($data));
        }

        $pagernator = Page::make($statistics_list, config('LIST_ROWS'), $curr_page, $list_count,
            false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
        $page = $pagernator->render();

        $this->assign([
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'server_list' => $server_list,
            'server_id' => $server_ids,
            'lists' => $statistics_list,
            'page' => $page,
            'date' => $date,
            'total_login_count' => $total_login_count,
            'total_role_count' => $total_role_count,
            'total_recharge_count' => $total_recharge_count,
            'total_recharge_amount' => $total_recharge_amount,
            'total_day_recharge_count' => $total_day_recharge_count,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'meta_title' => '综合统计'
        ]);
        return $this->fetch();

    }

    /**
     * 在线统计
     * @throws \think\Exception
     */
    public function online()
    {
        $server_list = ServerManage::getServerList();
        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }
        $filed = "logid,serverId,serverName,moduleName,value,logtime";
        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");

        $where[] = [
            ['moduleId', '=', 2],
            //=>['moduleName', '=', 'LOG_MODULE_WEB_ONLINE_PLAYER'],
            ['value', '>', 0]
        ];
        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
        }

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['serverId', 'between', [$start_server_id, $end_server_id]];
            $search = true;
        }
        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            $where[] = ['serverId', 'in', $server_ids];
        }
        $lists = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field($filed)
            ->where($where)
            ->where('logtime', 'between', [$start, $end])
            ->group('HOUR(logtime)')
            ->order('logtime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);


        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'server_list' => $server_list,
            'lists' => $lists,
            'server' => $server_id,
            'server_id' => $server_ids,
            'page' => $page,
            'date' => $date,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '在线统计'
        ]);
        return $this->fetch();
    }

    /**
     * 在线统计图表
     */
    public function online_charts()
    {
        $server_list = ServerManage::getServerList();
        $this->assign('server_list', $server_list);
        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }

        $this->assign('date', $date);
        //$time_slot = date('d', strtotime($date));
        $time_slot = trim(input('time_slot'));

        if (empty($time_slot) || $time_slot == -1) {
            $time_slot = date('d', time());//时间段未选择，默认当前时间小时
        }

        $this->assign('time_slot', $time_slot);

        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");
        $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        if (!$exists_table) {
            $this->error("数据表【{$table_name}】不存在！！！！");
        }
        $where[] = [
            ['moduleId', '=', 2],
        ];

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id)) {
            $server_ids = explode(',', $server_id);
        }

        if ($server_id) {
            $where[] = ['serverId', 'in', $server_ids];
        }

        $this->assign('server_id', $server_ids);
        $this->assign('server', $server_id);

        //统计昨日在线用户
        $onlineList = Db::connect('db_config_log')
            ->table($table_name)
            ->field(['MINUTE(logtime) as time', 'MAX(value) as value'])
            //->field(['HOUR(logtime) as time', 'MAX(value) as value','MINUTE(logtime) as min_time'])
            ->where($where)
            ->where('DAY(logtime)=' . $time_slot)
            ->group('DAY(logtime),HOUR(logtime), MINUTE(logtime)')
            ->order('logtime asc')
            ->select();

        $this->assign('onlineList', $onlineList);

        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day")));
        }
        $this->assign('dateArr', $dateArr);
        //统计最近一周在线用户
        $online7List = [];

        for ($j = 7; $j > 0; $j--) {
            $default_date = date('Y-m-d H:i:s', strtotime("-$j day"));
            $table_name_7day = 'Log' . date('Ymd', strtotime("-$j day"));

            $exists_table = Db::connect('db_config_log')->query('SHOW TABLES LIKE ' . "'" . $table_name_7day . "'");
            if ($exists_table) {
                $onlineInfo = Db::connect('db_config_log')
                    ->table($table_name_7day)
                    ->field('MAX(value) as value,logtime')
                    ->where($where)
                    ->limit(1)
                    ->select();
                array_push($online7List, $onlineInfo);
            } else {
                array_push($online7List, '');
            }
        }

        $this->assign('online7List', $online7List);
        return View::fetch();
    }

    /**
     * 注册统计
     */
    public function reguser()
    {
        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }

        //php获取今日开始时间戳和结束时间戳
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $server_id = trim(input('server_id'));
        $this->assign('server_id', $server_id);
        if ($server_id) {
            $where[] = ['serverId', '=', $server_id];
        }
        $lists = \app\admin\model\UserInfo::where('RegisterTime', 'between', [$start, $end])
            ->field('UserID,UserName,RegisterTime,ChannelID,Wx_UserName,Phone_UserName')
            ->order('UserID desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'date' => $date,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '用户注册统计'
        ]);

        return View::fetch();
    }

    /**
     * 用户注册统计图表
     */
    public function register_charts()
    {
        $server_list = ServerManage::getServerList();
        $date = trim(input('date'));

        //统计昨日注册用户
//        $registerList = \app\admin\model\UserInfo::field(['HOUR(RegisterTime) as time', 'COALESCE(count(UserID),0) as value'])
//            ->where('RegisterTime', '>', 'DATE_SUB(CURDATE(), INTERVAL 1 DAY)')->group('HOUR(RegisterTime)')
//            ->order('RegisterTime asc')
//            ->select();

        if ($date) {
            $start_day_time = date('Y-m-d', strtotime($date)) . ' 00:00:00';
            $end_day_time = date('Y-m-d', strtotime($date)) . ' 23:59:59';
        } else {
            $start_day_time = date('Y-m-d', time()) . ' 00:00:00';
            $end_day_time = date('Y-m-d', time()) . ' 23:59:59';
            $date = date('Y-m-d');
        }
        $registerList = \app\admin\model\UserInfo::field(['HOUR(RegisterTime) as time', 'COALESCE(count(UserID),0) as value'])
//            ->where('RegisterTime', '>', 'DATE_SUB(CURDATE(), INTERVAL 1 DAY)')->group('HOUR(RegisterTime)')
            ->where('RegisterTime', 'between', [$start_day_time, $end_day_time])
//            ->order('RegisterTime asc')
            ->group('HOUR(RegisterTime)')
            ->select();

        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day")));
        }

        //统计最近一周消费
        $register7List = [];
        for ($j = 7; $j > 0; $j--) {
            //$default_date = date('Y-m-d H:i:s', strtotime("-$j day"));
            if ($date) {
                $start_time = date('Y-m-d', strtotime("-$j day", strtotime($date))) . ' 0:00:00';
                $end_time = date('Y-m-d', strtotime("-$j day", strtotime($date))) . ' 23:59:59';
            } else {
                $start_time = date('Y-m-d', strtotime("-$j day")) . ' 0:00:00';
                $end_time = date('Y-m-d', strtotime("-$j day")) . ' 23:59:59';
            }

            $registerInfo = \app\admin\model\UserInfo::field('COALESCE(count(UserID),0) as value,IFNULL(RegisterTime,"' . $start_time . '") as RegisterTime')
                ->where('RegisterTime', 'between', [$start_time, $end_time])
                ->order('RegisterTime asc')
                ->select();
//                ->where('RegisterTime', '>', 'DATE_SUB(CURDATE(), INTERVAL ' . $j . ' DAY)')->order('RegisterTime asc')->select();
            array_push($register7List, $registerInfo);
        }

        View::assign([
            'dateArr' => $dateArr,
            'serverlist' => $server_list,
            'registerList' => $registerList,
            'register7List' => $register7List,
            'date' => $date,
            'meta_title' => '用户注册统计图表'
        ]);
        return View::fetch();
    }

    /**
     * 登录统计
     */
    public function loginuser()
    {
        $playername = trim(input('playerName'));
        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
        }

        $where[] = ['actionId', '=', 1];//=> ['actionName', '=', 'LOG_ACTION_TYPE_LOGIN'];
        if ($playername) {
            $where[] = ['playerName', 'like', "%$playername%"];
        }
        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");

        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        $this->assign('server_id', $server_id);
        if ($server_id) {
            $where[] = ['serverId', '=', $server_id];
        }

        $lists = Db::connect('db_config_log')
            ->table($table_name)
            ->where($where)
            ->order('logid desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'server_list' => $server_list,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'page' => $page,
            'date' => $date ? $date : date('Y-m-d'),
            'meta_title' => '登录统计'
        ]);

        return View::fetch();
    }

    /**
     * 统计用户登录图表
     */
    public function login_charts()
    {
        $table_name = 'log' . date('Ymd', strtotime("-1 day"));

        $where[] = ['actionId', '=', 1];//=>['actionName', '=', 'LOG_ACTION_TYPE_LOGIN']

        //统计昨日消费
        $loginList = Db::connect('db_config_log')
            ->table($table_name)
            ->field(['HOUR(logtime) as time', 'COALESCE(sum(value),0) as value'])
            ->where($where)
            ->group('HOUR(logtime)')
            ->order('logtime asc')
            ->select();
        $this->assign('loginList', $loginList);

        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day")));
        }
        $this->assign('dateArr', $dateArr);
        //统计最近一周消费
        $login7List = [];
        for ($j = 7; $j > 0; $j--) {
            $default_date = date('Y-m-d H:i:s', strtotime("-$j day"));
            $table_name_7day = 'Log' . date('Ymd', strtotime("-$j day"));
            $exists_table = Db::connect('db_config_log')->query('SHOW TABLES LIKE ' . "'" . $table_name_7day . "'");
            if ($exists_table) {
                $loginInfo = Db::connect('db_config_log')
                    ->table($table_name_7day)
                    ->field('COALESCE(sum(value),0) as value,IFNULL(logtime,"' . $default_date . '") as logtime')
                    ->where($where)
                    ->order('logtime asc')
                    ->select();
                array_push($login7List, $loginInfo);
            } else {
                array_push($login7List, '');
            }
        }
        $this->assign('login7List', $login7List);

        //统计前一天（当前日期的前一天）
        $top10 = Db::connect('db_config_log')
            ->table($table_name)
            ->field('playerName,sum(value) as value')
            ->where($where)->order('value desc')
            ->group('userId')
            ->limit(10)
            ->select();
        $totalValue = Db::connect('db_config_log')->table($table_name)->where($where)->sum('value');
        $this->assign('totalValue', $totalValue);
        $this->assign('top10', $top10);
        return View::fetch();
    }

    /**
     * 等级统计
     */
    public function userlevel()
    {
        $server_list = ServerManage::getServerList();
        $playername = trim(input('playerName'));
        $where[] = ['1', '=', '1'];
        if ($playername) {
            $where[] = ['nickname', 'like', "%$playername%"];
        }
        $server_id = trim(input('server_id'));
        $server_id = (!empty($server_id) && $server_id != 0) ? $server_id : 1;

        $lists = dbConfig($server_id)
            ->table('player')
            ->where($where)
            ->order('level desc,actor_id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'serverlist' => $server_list,
            'server_id' => $server_id,
            'playerName' => $playername,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '玩家等级统计'
        ]);
        return $this->fetch();
    }


    /**
     * 用户等级分布统计图表
     */
    public function level_charts()
    {
        $is_guild = false;
        if (GROUPID == GROUP_ID) {
            $is_guild = true;
            $s_ids = get_user_server_list(UID);
            $temp_server_ids = '';
            foreach ($s_ids as $key => $value) {
                $temp_server_ids .= $value['server_id'] . ',';
            }
            $ids = explode(',', rtrim($temp_server_ids, ","));
            $server_list = ServerManage::getServerListByIds($ids);
        } else {
            $server_list = ServerManage::getServerList();
        }
        $server_id = trim(input('server_id'));

        if ($is_guild) {
            if (empty($server_id) || $server_id == "0") {
                $s_id = get_user_server_by_id(UID);
                $resInfo = ServerManage::getServerInfoByGuild($s_id);
                if ($resInfo) {
                    $server_id = $resInfo['id'];
                }
            }
        } else {
            $server_id = (!empty($server_id) && $server_id != 0) ? $server_id : 1;
        }

        $this->assign([
            'server_id' => $server_id,
            'serverlist' => $server_list
        ]);
        return View::fetch();
    }

    /**
     * 用户等级分布统计
     */
    public function get_level_distribution()
    {
        $data = $_POST;
        $server_id = $data['server_id'];
        $server_id = (!empty($server_id) && $server_id != 0) ? $server_id : 1;

        $info = dbConfig($server_id)
            ->table('player')
            ->field('count(case when `level` between 1 and 60 then 1 end) as "60级以下",
                     count(case when `level` between 61 and 70 then 1 end) as "61-70级",
                     count(case when `level` between 71 and 80 then 1 end) as "71-80级",
                     count(case when `level` between 81 and 90 then 1 end) as "81-90级",
                     count(case when `level` between 91 and 100 then 1 end) as "91-100级",
                     count(case when `level` between 101 and 110 then 1 end) as "101-110级",
                     count(case when `level` between 111 and 120 then 1 end) as "111-120级",
                     count(case when `level` between 121 and 150 then 1 end) as "121-150级",
                     count(case when `level` between 151 and 170 then 1 end) as "151-170级",
                     count(case when `level` >170 then 1 end) as "170级以上"')
            ->select();
        $data = array();
        foreach ($info[0] as $key => $value) {
            $level = new levelChart();
            $level->name = $key;
            $level->value = $value;
            $data[] = $level;
        }
        return json(['data' => $data]);
    }

    /**
     * 用户等级分布汇总统计
     **/
    public function summary_level()
    {
        $server_id = trim(input('server_id'));
        $server_id_c = trim(input('server_id_c'));

        $server_list = ServerManage::getServerList();

        $where[] = ['use_status', '=', 1];
        if ((isset($server_id) && !empty($server_id)) && (isset($server_id_c) && !empty($server_id_c))) {
            $where[] = ['id', 'between', [$server_id, $server_id_c]];
        } else {
            $server_id = 1;
            $server_id_c = 10;
            $where[] = ['id', 'between', [$server_id, $server_id_c]];
        }

        /** 等级统计初始化 **/
        $total_level_1 = 0;
        $total_level_2 = 0;
        $total_level_3 = 0;
        $total_level_81 = 0;
        $total_level_82 = 0;
        $total_level_83 = 0;
        $total_level_84 = 0;
        $total_level_85 = 0;
        $total_level_86 = 0;
        $total_level_87 = 0;
        $total_level_88 = 0;
        $total_level_89 = 0;
        $total_level_90 = 0;

        $total_level_5 = 0;
        $total_level_6 = 0;
        $total_level_7 = 0;
        $total_level_8 = 0;
        $total_level_9 = 0;
        $total_level_10 = 0;
        $data = array();
        $show_server = ServerList::where($where)->field('id')->select();
        foreach ($show_server as $s_value) {
            $info = dbConfigByReadBase($s_value['id'])
                ->table('player')
                ->field('count(case when `level` between 1 and 60 then 1 end) as "level1",
                     count(case when `level` between 61 and 70 then 1 end) as "level2",
                     count(case when `level` between 71 and 80 then 1 end) as "level3", 
                      count(case when `level`=81 then 1 end) as "level_81",
										 count(case when `level`=81 then 1 end) as "level_81",
										 count(case when `level`=82 then 1 end) as "level_82",
										 count(case when `level`=83 then 1 end) as "level_83",
										 count(case when `level`=84 then 1 end) as "level_84",
										 count(case when `level`=85 then 1 end) as "level_85",
										 count(case when `level`=86 then 1 end) as "level_86",
										 count(case when `level`=87 then 1 end) as "level_87",
										 count(case when `level`=88 then 1 end) as "level_88",
										 count(case when `level`=89 then 1 end) as "level_89",
										 count(case when `level`=90 then 1 end) as "level_90",
                     count(case when `level` between 91 and 100 then 1 end) as "level5",
                     count(case when `level` between 101 and 110 then 1 end) as "level6",
                     count(case when `level` between 111 and 120 then 1 end) as "level7",
                     count(case when `level` between 121 and 150 then 1 end) as "level8",
                     count(case when `level` between 151 and 170 then 1 end) as "level9",
                     count(case when `level` >170 then 1 end) as "level10"')
                ->select();

            foreach ($info as $key => $value) {
                $level = new SummaryLevelChart();
                $level->server_id = $s_value['id'];
                $level->level_1 = $value['level1'];
                $total_level_1 += $value['level1'];
                $level->level_2 = $value['level2'];
                $total_level_2 += $value['level2'];
                $level->level_3 = $value['level3'];
                $total_level_3 += $value['level3'];
                $level->level_81 = $value['level_81'];
                $level->level_82 = $value['level_82'];
                $level->level_83 = $value['level_83'];
                $level->level_84 = $value['level_84'];
                $level->level_85 = $value['level_85'];
                $level->level_86 = $value['level_86'];
                $level->level_87 = $value['level_87'];
                $level->level_88 = $value['level_88'];
                $level->level_89 = $value['level_89'];
                $level->level_90 = $value['level_90'];
                $total_level_81 += $value['level_81'];
                $total_level_82 += $value['level_82'];
                $total_level_83 += $value['level_83'];
                $total_level_84 += $value['level_84'];
                $total_level_85 += $value['level_85'];
                $total_level_86 += $value['level_86'];
                $total_level_87 += $value['level_87'];
                $total_level_88 += $value['level_88'];
                $total_level_89 += $value['level_89'];
                $total_level_90 += $value['level_90'];
                $level->level_5 = $value['level5'];
                $total_level_5 += $value['level5'];
                $level->level_6 = $value['level6'];
                $total_level_6 += $value['level6'];
                $level->level_7 = $value['level7'];
                $total_level_7 += $value['level7'];
                $level->level_8 = $value['level8'];
                $total_level_8 += $value['level8'];
                $level->level_9 = $value['level9'];
                $total_level_9 += $value['level9'];
                $level->level_10 = $value['level10'];
                $total_level_10 += $value['level10'];
                $data[] = $level;
            }
        }

        View::assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'server_id_c' => $server_id_c,
            'lists' => objectToArray($data),
            'total_level_1' => $total_level_1,
            'total_level_2' => $total_level_2,
            'total_level_3' => $total_level_3,
            'total_level_81' => $total_level_81,
            'total_level_82' => $total_level_82,
            'total_level_83' => $total_level_83,
            'total_level_84' => $total_level_84,
            'total_level_85' => $total_level_85,
            'total_level_86' => $total_level_86,
            'total_level_87' => $total_level_87,
            'total_level_88' => $total_level_88,
            'total_level_89' => $total_level_89,
            'total_level_90' => $total_level_90,
            'total_level_5' => $total_level_5,
            'total_level_6' => $total_level_6,
            'total_level_7' => $total_level_7,
            'total_level_8' => $total_level_8,
            'total_level_9' => $total_level_9,
            'total_level_10' => $total_level_10,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '等级分布汇总'
        ]);
        return View::fetch();
    }


    /**
     * 用户在线时长等级分布统计
     */
    public function get_online_duration_leve_distribution()
    {
        $server_id = $_GET['server_id'];
        $server_id = (!empty($server_id) && $server_id != 0) ? $server_id : 1;

        $info = dbConfig($server_id)
            ->table('player')
            ->field('count(case when `online_time_all` between 60 and 600 then 1 end) as "1-10分钟",
                     count(case when `online_time_all` between 601 and 1800 then 1 end) as "11-30分钟",
                     count(case when `online_time_all` between 1801 and 3600 then 1 end) as "31-60分钟",
                     count(case when `online_time_all` between 3601 and 7200 then 1 end) as "61-120分钟",
                     count(case when `online_time_all` >7200 then 1 end) as "120分钟以上"')
            ->select();
        $data = array();
        foreach ($info[0] as $key => $value) {
            $level = new levelChart();
            $level->name = $key;
            $level->value = $value;
            $data[] = $level;
        }
        return json(['data' => $data]);
    }

    /**
     * 用户在线时长分布汇总统计
     **/
    public function summary_duration()
    {
        $server_id = trim(input('server_id'));
        $server_id_c = trim(input('server_id_c'));

        $server_list = ServerManage::getServerList();

        $where[] = ['use_status', '=', 1];
        if ((isset($server_id) && !empty($server_id)) && (isset($server_id_c) && !empty($server_id_c))) {
            $where[] = ['id', 'between', [$server_id, $server_id_c]];
        } else {
            $server_id = 1;
            $server_id_c = 10;
            $where[] = ['id', 'between', [$server_id, $server_id_c]];
        }

        $total_online_10 = 0;
        $total_online_30 = 0;
        $total_online_60 = 0;
        $total_online_120 = 0;
        $total_online = 0;
        $data = array();
        $show_server = ServerList::where($where)->field('id')->select();
        foreach ($show_server as $s_value) {
            $info = dbConfig($s_value['id'])
                ->table('player')
                ->field('count(case when `online_time_all` between 60 and 600 then 1 end) as "online10",
                     count(case when `online_time_all` between 601 and 1800 then 1 end) as "online30",
                     count(case when `online_time_all` between 1801 and 3600 then 1 end) as "online60",
                     count(case when `online_time_all` between 3601 and 7200 then 1 end) as "online120",
                     count(case when `online_time_all` >7200 then 1 end) as "online"')
                ->select();

            foreach ($info as $key => $value) {
                $duration = new SummaryOnlineDuration();
                $duration->server_id = $s_value['id'];
                $duration->online10 = $value['online10'];
                $total_online_10 += $value['online10'];
                $duration->online30 = $value['online30'];
                $total_online_30 += $value['online30'];
                $duration->online60 = $value['online60'];
                $total_online_60 += $value['online60'];
                $duration->online120 = $value['online120'];
                $total_online_120 += $value['online120'];
                $duration->online = $value['online'];
                $total_online += $value['online'];
                $data[] = $duration;
            }
        }

        View::assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'server_id_c' => $server_id_c,
            'lists' => objectToArray($data),
            'total_online_10' => $total_online_10,
            'total_online_30' => $total_online_30,
            'total_online_60' => $total_online_60,
            'total_online_120' => $total_online_120,
            'total_online' => $total_online,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '在线时长分布汇总'
        ]);
        return View::fetch();
    }

    /**
     * 充值统计
     */
    public function recharge_old()
    {
        $player_name = trim(input('playerName'));

        $map = [
            ['moduleId', '=', 1]//=>['moduleName', '=', 'LOG_MODULE_WEB_RECHARGE']
        ];
        $map1 = [
            ['moduleId', '=', 3]//=>['moduleName', '=', 'LOG_MODULE_CHANNEL_RECHARGE']
        ];
        $where[] = ['value', '>', 0];
        if ($player_name) {
            $where[] = ['playerName', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['serverId', '=', $server_id];
        }

        $server_list = ServerManage::getServerList();

        $date = trim(input('date'));
        if (empty($date)) $date = date('Y-m-d');

        $table_name = "Log" . date('Ymd', strtotime($date));
        $lists = Db::connect('db_config_log')->table($table_name)
            ->where($where)
            ->whereOr([$map, $map1])
            ->order('logid desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'date' => $date,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '充值统计'
        ]);
        return $this->fetch('recharge');
    }


    public function recharge()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('playerName'));

        $date = trim(input('date'));
        if (empty($date)) $date = date('Y-m-d');
        $where[] = ['add_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59']];
        if ($player_name) {
            $where[] = ['playerName', 'like', "%$player_name%"];
        }

        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));

        //区服范围查询
        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = [
                ['server_id', '>=', $start_server_id],
                ['server_id', '<=', $end_server_id]
            ];
            $search = true;
        }

        $server_id = trim(input('server_id'));
        if ($server_id && $search == false) {
            $where[] = ['server_id', '=', $server_id];
        }


        $lists = Db::connect('db_config_main')->table('recharge_data')
            ->where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'date' => $date,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '充值统计'
        ]);
        return $this->fetch('recharge');
    }

    /**
     * 充值统计图表
     */
    public function recharge_charts()
    {
        $serverlist = ServerManage::getServerList();
        $this->assign('serverlist', $serverlist);

        $where[] = ['status', '=', 1];
        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        $this->assign('server_id', $server_id);

        $date = trim(input('date'));
        if (empty($date)) $date = date('Y-m-d');
        $this->assign('date', $date);

        $rechargeList = Db::connect('db_config_main')
            ->table('recharge_data')
            ->field(['HOUR(add_time) as time', 'COALESCE(sum(money),0) as value'])
            ->where($where)
            ->whereTime('add_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->group('HOUR(add_time)')
            ->order('add_time asc')
            ->select();

        $this->assign('rechargeList', $rechargeList);

        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day", strtotime($date))));
        }
        $this->assign('dateArr', $dateArr);

        //统计最近一周消费
        $cost7List = [];
        for ($j = 7; $j > 0; $j--) {
            $default_date = date('Y-m-d H:i:s', strtotime("-$j day", strtotime($date)));
            $cost_time = date('Ymd', strtotime("-$j day", strtotime($date)));

            $costInfo = Db::connect('db_config_main')
                ->table('recharge_data')
                ->field('COALESCE(sum(money),0) as value,IFNULL(add_time,"' . $default_date . '") as add_time')
                ->where($where)
                ->whereTime('add_time', 'between', [$cost_time . ' 00:00:00', $cost_time . ' 23:59:59'])
                ->order('add_time asc')
                ->select();
            array_push($cost7List, $costInfo);
        }
        $this->assign('cost7List', $cost7List);

        //统计前一天（当前日期的前一天）

        $top10 = Db::connect('db_config_main')
            ->table('recharge_data')
            ->field('server_id,user_id,COALESCE(sum(money),0) as value')
            ->where($where)
            ->whereTime('add_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->order('value desc')
            ->group('user_id')
            ->limit(10)
            ->select();
        $totalValue = Db::connect('db_config_main')
            ->table('recharge_data')
            ->where($where)
            ->whereTime('add_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->sum('money');
        $this->assign('totalValue', $totalValue);
        $this->assign([
            'top10' => $top10,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
        ]);

        return View::fetch();
    }

    /**
     * 消费统计
     */
    public function cost()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('playerName'));

        $cost_scene_list = GameLogActionManage::getCostSceneList();

        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }
        $filed = "logid,serverId,serverName,userId,playerName,moduleId,moduleName,value,logtime";
        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");

        $where[] = ['actionId', '=', 9]; //=>['actionName', '=', 'LOG_ACTION_TYPE_EXPEND_YUANBAO'];

        if ($player_name) {
            $where[] = ['playerName', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
        /** @var TYPE_NAME $server_id */
        if ($server_id) $where[] = ['serverId', '=', $server_id];

        $cost_scene = trim(input('cost_scene'));
        if (isset($cost_scene)) {
            $where[] = ['moduleName', '=', $cost_scene];
        }

        $lists = Db::connect('db_config_log')
            ->table($table_name)
            ->field($filed)
            ->where($where)
            ->where('logtime', 'between', [$start, $end])
            ->order('logtime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'serverlist' => $server_list,
            'playerName' => $player_name,
            'date' => $date,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'cost_scene_list' => $cost_scene_list,
            'cost_scene' => $cost_scene,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '消费统计'
        ]);
        return $this->fetch('cost');
    }

    /**
     * 消费统计图表
     */
    public function cost_charts()
    {
        $server_list = ServerManage::getServerList();
        $this->assign('serverlist', $server_list);
        $where[] = ['actionId', '=', 9]; //=>['actionName', '=', 'LOG_ACTION_TYPE_EXPEND_YUANBAO'];

        $server_id = trim(input('server_id'));
        if ($server_id) $where[] = ['serverId', '=', $server_id];

        $date = trim(input('date'));
        if (empty($date)) $date = date('Y-m-d');
        $this->assign('date', $date);

        $table_name = "Log" . date('Ymd', strtotime($date));
        //统计昨日消费
        $costList = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field(['serverId', 'HOUR(logtime) as time', 'COALESCE(sum(value),0) as value'])
            ->where($where)
            ->group('HOUR(logtime)')
            ->order('logtime asc')
            ->select();
        $this->assign('costList', $costList);
        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day")));
        }
        $this->assign('dateArr', $dateArr);

        //统计最近一周消费
        $cost7List = [];
        for ($j = 7; $j > 0; $j--) {
            $default_date = date('Y-m-d H:i:s', strtotime("-$j day"));
            $table_name_7cost = 'Log' . date('Ymd', strtotime("-$j day"));
            $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name_7cost . "'");
            if ($exists_table) {
                $costInfo = Db::connect('db_config_log_read')
                    ->table($table_name_7cost)
                    ->field('COALESCE(sum(value),0) as value,IFNULL(logtime,"' . $default_date . '") as logtime')
                    ->where($where)
                    ->order('logtime asc')
                    ->select();
                array_push($cost7List, $costInfo);
            } else {
                array_push($cost7List, '');
            }
        }
        $this->assign('cost7List', $cost7List);

        //统计前一天（当前日期的前一天）
        $top10 = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field('serverId,userId,playerName,COALESCE(sum(value),0) as value')
            ->where($where)
            ->order('value desc')
            ->group('userId')
            ->limit(10)
            ->select();
        $totalValue = Db::connect('db_config_log_read')->table($table_name)->where($where)->sum('value');
        $this->assign('totalValue', $totalValue);
        $this->assign([
            'top10' => $top10,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '消费统计图表'
        ]);

        return View::fetch();
    }


    /**
     * 储蓄统计数据列表
     */
    public function deposit()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('playerName'));

        $deposit_scene_list = GameLogActionManage::getAddSceneList();

        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
            $date = date('Y-m-d');
        }
        $filed = "logid,serverId,serverName,userId,playerName,moduleName,value,logtime";

        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");

        $where[] = ['actionId', '=', 8]; //=>['actionName', '=', 'LOG_ACTION_TYPE_ADD_YUANBAO'];

        if ($player_name) {
            $where[] = ['playerName', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
        /** @var TYPE_NAME $server_id */
        if ($server_id) $where[] = ['serverId', '=', $server_id];

        $deposit_scene = trim(input('deposit_scene'));
        if ($deposit_scene) $where[] = ['moduleName', '=', $deposit_scene];

        $lists = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field($filed)
            ->where($where)
            ->where('logtime', 'between', [$start, $end])
            ->order('logtime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'serverlist' => $server_list,
            'playerName' => $player_name,
            'date' => $date,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'deposit_scene_list' => $deposit_scene_list,
            'deposit_scene' => $deposit_scene,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '储蓄统计数据列表'
        ]);
        return $this->fetch('deposit');
    }

    /**
     * 储蓄统计图表
     */
    public function deposit_charts()
    {
        $server_list = ServerManage::getServerList();
        $this->assign('serverlist', $server_list);
        $where[] = ['actionId', '=', 8];

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['serverId', '=', $server_id];
        } else {
            $server_id = 1;
        }
        $this->assign('server_id', $server_id);

        $date = trim(input('date'));
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $this->assign('date', $date);

        $table_name = "Log" . date('Ymd', strtotime($date));
        //统计昨日储蓄
        $depositList = Db::connect('db_config_log_read')
            ->table($table_name)->field(['HOUR(logtime) as time', 'COALESCE(sum(value),0) as value'])
            ->where($where)
            ->group('HOUR(logtime)')
            ->order('logtime asc')
            ->select();
        $this->assign('depositList', $depositList);

        //初始化一周日期
        $dateArr = [];
        for ($i = 7; $i > 0; $i--) {
            array_push($dateArr, date('m-d', strtotime("-$i day")));
        }
        $this->assign('dateArr', $dateArr);

        //统计最近一周消费
        $cost7List = [];
        for ($j = 7; $j > 0; $j--) {
            $default_date = date('Y-m-d H:i:s', strtotime("-$j day"));
            $table_name_7d = 'Log' . date('Ymd', strtotime("-$j day"));
            $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name_7d . "'");
            if ($exists_table) {
                $costInfo = Db::connect('db_config_log_read')
                    ->table($table_name_7d)
                    ->field('COALESCE(sum(value),0) as value,IFNULL(logtime,"' . $default_date . '") as logtime')
                    ->where($where)
                    ->order('logtime asc')
                    ->select();
                array_push($cost7List, $costInfo);
            } else {
                array_push($cost7List, '');
            }
        }
        $this->assign('cost7List', $cost7List);
        $curr_top10 = dbConfigByReadBase($server_id)->table('player')->field('actor_id,nickname,yuanbao')->order('yuanbao desc')->limit(10)->select();

        //统计前一天（当前日期的前一天）
        $top10 = Db::connect('db_config_log_read')
            ->table("Log" . date('Ymd', strtotime('-1 day')))
            ->field('serverId,userId,playerName,COALESCE(sum(value),0) as value')
            ->where($where)
            ->order('value desc')
            ->group('userId')
            ->limit(10)
            ->select();
        $totalValue = Db::connect('db_config_log_read')->table("Log" . date('Ymd', strtotime('-1 day')))->where($where)->sum('value');
        $curr_totalValue = dbConfigByReadBase($server_id)->table('player')->sum('yuanbao');
        $this->assign([
            'top10' => $top10,
            'curr_top10' => $curr_top10,
            'totalValue' => $totalValue,
            'curr_totalValue' => $curr_totalValue,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '储蓄统计图表'
        ]);
        return View::fetch();
    }

    /**
     * LTV
     * 统计前10天每个服务器充值信息，LTV
     */
    public function cycle_recharge()
    {
        $where[] = ['money', '>', 0];
        $server_list = ServerManage::getServerList();
        $start_time = trim(input('start_time'));
        $end_time = trim(input('end_time'));
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
        }

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['server_id', 'between', [$start_server_id, $end_server_id]];
            $search = true;
        }
        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            $where[] = ['server_id', 'in', $server_ids];
        }

        //查询日期范围
        if (!empty($start_time) && !empty($end_time)) {
            $where[] = ['add_time', 'between', [$start_time, $end_time]];
        }

        //获取充值过的服务器列表
        $recharge_server_lists = Db::connect('db_config_main_read')
            ->table('recharge_data')
            ->where($where)
            ->field('server_id,add_time')
            ->group('server_id')
            ->order('server_id asc')
            ->select();

        $recharge_day = 0;
        $lists = array();
        $recharge_server_lists_count = count($recharge_server_lists);
        if ($recharge_server_lists_count > 0) {
            for ($i = 0; $i < $recharge_server_lists_count; $i++) {
                if ($i == 0) {
                    $curr_date = date("Y-m-d");
                    $compare_date = date('Y-m-d', strtotime("-1 day", strtotime($recharge_server_lists[$i]['add_time'])));
                    $d1 = strtotime($curr_date);
                    $d2 = strtotime($compare_date);
                    $differ = round(($d1 - $d2) / 3600 / 24);
                    $recharge_day = $differ;
                }

                $s_id = $recharge_server_lists[$i]['server_id'];
                //当前服务器首次充值时间的前一天
                $s_time = date('Y-m-d', strtotime("-1 day", strtotime($recharge_server_lists[$i]['add_time'])));
                $where[] = ['server_id', '=', $s_id];

                /**
                 * SELECT r.num,r.date,IFNULL(b.count,0) as total,b.date,r.server_id
                 * FROM((select @num:=@num+1 as num,date_format(adddate('2020-12-16', INTERVAL @num DAY),'%Y-%m-%d') as date,server_id from recharge_data ,(select @num:=0) t where adddate('2020-12-16', INTERVAL @num DAY) <= DATE_FORMAT(subdate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')  and server_id=1 order by date )) r
                 * LEFT JOIN (SELECT SUM(money) as count, DATE_FORMAT(add_time,'%Y-%m-%d') as date FROM `recharge_data` WHERE add_time BETWEEN '2020-12-16' and DATE_FORMAT(adddate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')
                 * and server_id=1 GROUP BY date)b
                 * on r.date = b.date;
                 **/


                $sql_str = "SELECT r.num,r.date,IFNULL(b.count,0) as total,b.date,r.real_server_id as server_id
                FROM((select @num:=@num+1 as num,date_format(adddate('$s_time', INTERVAL @num DAY),'%Y-%m-%d') as date,real_server_id from recharge_data ,
                (select @num:=0) t where adddate('$s_time', INTERVAL @num DAY) <= DATE_FORMAT(subdate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')  and real_server_id={$s_id} 
                order by date )) r
                LEFT JOIN (SELECT SUM(money) as count, DATE_FORMAT(add_time,'%Y-%m-%d') as date FROM `recharge_data` 
                WHERE add_time BETWEEN '$s_time' and DATE_FORMAT(adddate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')
                and real_server_id={$s_id} GROUP BY date) b
                on r.date = b.date ";

                Log::write("cycle recharge execute sql:");
                Log::write($sql_str);


                $Model = new \app\admin\model\RechargeData();
                $recharge_lists = $Model->query($sql_str);

                $recharge_count = count($recharge_lists);
                $temp = '';
                $total = 0;
                $temp_ltv_total = '';
                $info = new LifeTimeValue();

                for ($k = 0; $k < $recharge_count; $k++) {
                    if ($k == 0) {
                        $first_date = $recharge_lists[$k]['date'];
                        $info->server_id = $s_id;
                        $info->date = $first_date;
                        $info->first_role_count = get_player_role_count_by_first_day($s_id, $first_date);
                        $info->second_role_count = get_player_role_count_by_second_day($s_id, $first_date);
                        $info->two_days_role_count = $info->first_role_count + $info->second_role_count; //两日创建角色数
                        $info->total_role_count = get_player_role_count($s_id, '');
                    }

                    if ($recharge_lists[$k]['total'] == 0) {
                        $temp .= 0 . ',';
                    } else {
                        $temp .= $recharge_lists[$k]['total'] . ',';
                    }
                    $temp_ltv_arr = explode(',', rtrim($temp, ','));
                    $temp_ltv_total .= array_sum($temp_ltv_arr) . ',';
                    if ($recharge_lists[$k]['total'] != 0) {
                        $total += $recharge_lists[$k]['total'];
                    }
                }

                $temp_arr = explode(',', rtrim($temp, ','));

                $temp_count = count($temp_arr);
                $days = intval($recharge_day - $temp_count);
                if ($days > 0) {
                    for ($r = 0; $r < $days; $r++) {
                        if (isset($recharge_lists[$k]['total']) && $recharge_lists[$k]['total'] != 0) {
                            $temp_ltv_total .= $recharge_lists[$k]['total'] . ',';
                        } else {
                            $temp_ltv_total .= 0 . ',';
                        }
                    }
                }

                $temp_arr_1 = explode(',', rtrim($temp_ltv_total, ','));
                $info->recharge = objectToArray($temp_arr_1);
                $info->total = $total;
                $info_arr = objectToArray($info);
                array_push($lists, $info_arr);
            }
        }
        $page = '';
        $this->assign([
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'start_time' => $start_time ? $start_time : date('Y-m-d 00:00:00'),
            'end_time' => $end_time ? $end_time : date('Y-m-d 23:59:59'),
            'lists' => $lists,
            'page' => $page,
            'recharge_count' => $recharge_day,
            'server_list' => $server_list,
            'server_id' => $server_ids,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '付费留存'
        ]);
        return View::fetch();
    }

    /**
     * 付费留存
     **/
    public function pay_retained()
    {
        $where[] = ['1', '=', 1];
        $server_list = ServerManage::getServerList();
        $start_time = trim(input('start_time'));
        $end_time = trim(input('end_time'));
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
        }

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['server_id', 'between', [$start_server_id, $end_server_id]];
            $search = true;
        }
        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            $where[] = ['server_id', 'in', $server_ids];
        }

        //查询日期范围
        if (!empty($start_time) && !empty($end_time)) {
            $where[] = ['add_time', 'between', [$start_time, $end_time]];
        }

        //获取充值过的服务器列表
        $recharge_server_lists = \app\admin\model\RechargeData::where($where)
            ->field('server_id,add_time')
            ->group('server_id')
            ->order('server_id asc')
            ->select();

        $recharge_day = 0;
        $lists = array();
        if (count($recharge_server_lists) > 0) {
            for ($i = 0; $i < count($recharge_server_lists); $i++) {
                if ($i == 0) {
                    $curr_date = date("Y-m-d");
                    $compare_date = date('Y-m-d', strtotime("-1 day", strtotime($recharge_server_lists[$i]['add_time'])));
                    $d1 = strtotime($curr_date);
                    $d2 = strtotime($compare_date);
                    $differ = round(($d1 - $d2) / 3600 / 24);
                    $recharge_day = $differ;
                }

                $s_id = $recharge_server_lists[$i]['server_id'];
                //当前服务器首次充值时间的前一天
                $s_time = date('Y-m-d', strtotime("-1 day", strtotime($recharge_server_lists[$i]['add_time'])));
                $where[] = ['server_id', '=', $s_id];


                $sql_str = "SELECT r.num,r.date,IFNULL(b.count,0) as total,b.date,r.real_server_id as server_id
                FROM((select @num:=@num+1 as num,date_format(adddate('$s_time', INTERVAL @num DAY),'%Y-%m-%d') as date,real_server_id from recharge_data ,
                (select @num:=0) t where adddate('$s_time', INTERVAL @num DAY) <= DATE_FORMAT(subdate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')  and real_server_id={$s_id} 
                order by date )) r
                LEFT JOIN (SELECT COUNT(DISTINCT user_id) as count, DATE_FORMAT(add_time,'%Y-%m-%d') as date FROM `recharge_data` 
                WHERE add_time BETWEEN '$s_time' and DATE_FORMAT(adddate(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')
                and real_server_id={$s_id} GROUP BY date) b
                on r.date = b.date ";

                $Model = new \app\admin\model\RechargeData();
                $recharge_lists = $Model->query($sql_str);
                $recharge_count = count($recharge_lists);
                $temp = '';
                $temp_ltv_total = '';
                $info = new PayRetained();

                for ($k = 0; $k < $recharge_count; $k++) {
                    if ($k == 0) {
                        $info->server_id = $s_id;
                        $info->date = $recharge_lists[$k]['date'];
                    }

                    if ($recharge_lists[$k]['total'] == 0 || !isset($recharge_lists[$k]['total'])) {
                        $temp .= 0 . ',';
                    } else {
                        $temp .= $recharge_lists[$k]['total'] . ',';
                    }
                }
                $temp_arr = explode(',', rtrim($temp, ','));

                $temp_count = count($temp_arr);
                $days = intval($recharge_day - $temp_count);

                if ($days > 0) {
                    for ($r = 0; $r < $days; $r++) {
                        if (isset($recharge_lists[$k]['total']) && $recharge_lists[$k]['total'] != 0) {
                            $temp .= $recharge_lists[$k]['total'] . ',';
                        } else {
                            $temp .= 0 . ',';
                        }
                    }
                }
                $temp_arr_1 = explode(',', rtrim($temp, ','));
                $info->recharge = objectToArray($temp_arr_1);
                $info_arr = objectToArray($info);
                array_push($lists, $info_arr);
            }
        }

        $page = '';
        $this->assign([
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'start_time' => $start_time ? $start_time : date('Y-m-d 00:00:00'),
            'end_time' => $end_time ? $end_time : date('Y-m-d 23:59:59'),
            'lists' => $lists,
            'page' => $page,
            'recharge_count' => $recharge_day,
            'server_list' => $server_list,
            'server_id' => $server_ids,
            'empty' => '<td class="empty" colspan="4">暂无数据</td>',
            'meta_title' => '付费留存'
        ]);
        return View::fetch();
    }


    /**
     * 用户留存率统计
     **/
    public function retention()
    {
        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        //根据（传值）服务器ID查询
        $lists = array();//查询返回的数据列表
        $serverInfo = ServerList::find($server_id);
        $open_time = $serverInfo['open_time'];
        $curr_date = date('Y-m-d', time());

        //$format_open_date = date('Y-m-d', $open_time);//格式化日期格式Y-m-d
        $differ = ceil(intval(strtotime($curr_date) - $open_time) / 86400);//开服到当前日期天数
        $differ = $differ < 1 ? 1 : $differ;
        for ($j = 0; $j <= $differ; $j++) {
            $log_name = "Log" . date('Ymd', strtotime("+$j day", $open_time));
            if ($this->exists_table($log_name)) {
                $field = "date_format(log.logtime,'%Y-%m-%d') as log_date,log.serverId as log_server_id,p.actor_id,p.create_server_id";
                $query = Db::connect('db_config_log_read')
                    ->table($log_name)
                    ->alias('log')
                    ->join('cq_game' . $server_id . '.player p', 'p.actor_id=log.userId', 'LEFT')
                    ->field($field)
                    ->where('log.actionId=1 and p.create_server_id=' . $server_id)
                    ->group('log.userId')
                    ->select();

                $query_count = count($query);

                if ($query_count > 0) {
                    $item['log_date'] = $query[0]['log_date'];
                    $item['user_count'] = $query_count;
                    $log_server_id = $query[0]['log_server_id'];
                    $log_date = $item['log_date'];

                    //注册用户数
                    $item['register_user'] = real_server_register_user($log_server_id, $log_date);
                    //创建角色数
                    $item['create_role_user'] = get_player_role_count($log_server_id, $log_date);
                    //付费人数(充值用户数)
                    $item['recharge_user'] = real_server_recharge_user($log_server_id, $log_date);
                    //充值总金额
                    $item['recharge_total'] = real_server_recharge_total($log_server_id, $log_date);

                    $item['server_id'] = $log_server_id;
                    $item['create_server_id'] = $query[0]['create_server_id'];

                    if ($item['log_date'] == date('Y-m-d', $open_time)) {
                        $item['two_days_register_user'] = get_player_role_count_by_first_day($log_server_id, $log_date);
                    } else {
                        $item['two_days_register_user'] = intval(get_player_role_count_by_first_day($log_server_id, date('Y-m-d', $open_time))
                            + get_player_role_count_by_first_day($log_server_id, date('Y-m-d', strtotime("+1 day", $open_time))));
                    }

                    //付费率:付费人数/DAU(日活跃人数)
                    if ($item['recharge_user'] > 0 && $item['user_count'] > 0) {
                        $item['pay_rate'] = round(($item['recharge_user'] / $item['user_count']), 2);
                    }
                    if ($item['recharge_total'] > 0 && $item['two_days_register_user'] > 0) {
                        $item['ARPU'] = round(($item['recharge_total'] / $item['two_days_register_user']), 2);//充值总数/注册人数(只统计前两天)
                    } else {
                        $item['ARPU'] = 0;
                    }
                    if ($item['recharge_total'] > 0 && $item['recharge_user'] > 0) {
                        $item['ARPPU'] = round(($item['recharge_total'] / $item['recharge_user']), 2);
                    } else {
                        $item['ARPPU'] = 0;
                    }

                    //转化率:创建角色数/注册人数(只统计前两天)
                    if ($item['create_role_user'] > 0 && $item['register_user']) {
                        $item['trans_rate'] = round($item['create_role_user'] / $item['register_user'], 2);
                    } else {
                        $item['trans_rate'] = 0;
                    }

                    //留存率 = (日活用户-新注册用户)/两日注册用户总人数
                    if ($item['log_date'] == date('Y-m-d', $open_time)) {
                        if ($item['user_count'] > 0 && $item['two_days_register_user'] > 0) {
                            $item['keep_rate'] = round($item['user_count'] / $item['two_days_register_user'], 2);
                        } else {
                            $item['keep_rate'] = 0;
                        }
                    } else {
                        if ($item['user_count'] - $item['register_user'] > 0 && $item['two_days_register_user'] > 0) {
                            $item['keep_rate'] = round(($item['user_count'] - $item['register_user']) / $item['two_days_register_user'], 2);
                        } else {
                            $item['keep_rate'] = 0;
                        }
                    }
                    array_push($lists, $item);
                }
            }
        }

        View::assign([
            'lists' => $lists,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'empty' => '<td class="empty" colspan="12">暂无数据</td>',
            'meta_title' => '用户留存率统计'
        ]);
        return View::fetch();
    }

    /**
     * 检测数据表是否存在
     * @param $table_name
     * @return
     * @throws \think\Exception
     */
    public function exists_table($table_name)
    {
        return Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
    }

    /**
     * 根据已选择的服务器ID筛选被合服的服务器ID或范围
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getServerListBySelectId($id)
    {
        $where[] = [
            ['id', '>', $id],
            ['use_status', '=', 1]
        ];
        return ServerList::where($where)->select();
    }
}


/**
 * 对象转数组
 * @param $object
 * @return array
 */
function objectToArray($object)
{
    $temp = is_object($object) ? get_object_vars($object) : $object;

    $arr = array();
    foreach ($temp as $k => $v) {
        $v = (is_array($v) || is_object($v)) ? objectToArray($v) : $v;
        $arr [$k] = $v;
    }
    return $arr;
}


/**
 * 等级分布json元素
 */
class levelChart
{
    public $name;
    public $value;
}

/**
 * 等级分布汇总
 **/
class SummaryLevelChart
{
    public $server_id;
    public $level_1;
    public $level_2;
    public $level_3;
    public $level_81;
    public $level_82;
    public $level_83;
    public $level_84;
    public $level_85;
    public $level_86;
    public $level_87;
    public $level_88;
    public $level_89;
    public $level_90;
    public $level_5;
    public $level_6;
    public $level_7;
    public $level_8;
    public $level_9;
    public $level_10;
}

/**
 * 在线时长分布汇总
 **/
class SummaryOnlineDuration
{
    public $server_id;
    public $online10;
    public $online30;
    public $online60;
    public $online120;
    public $online;
}


/**
 * 付费留存实体类
 */
class PayRetained
{
    public $server_id;
    public $date;
    public $recharge;
}


/**
 * LTV统计实体类
 **/
class LifeTimeValue extends PayRetained
{
    public $first_role_count;//首日注册数
    public $second_role_count;//次日注册数
    public $two_days_role_count;
    public $total_role_count;//注册总人数
    public $total;
}


/**
 *
 **/
class PerRecharge
{
    public $key;
    public $value;
}


/**
 * 综合统计实体类
 **/
class comprehensive
{
    public $logtime;
    public $opentime;//具体开服时间
    public $server_id;
    public $server_name;//区服(ID+区服名称)
    public $login_count;//登录账号数
    public $role_count;//创建角色数
    public $recharge_count;//充值人数
    public $recharge_amount;//充值金额
    public $daily_activity;//日活跃
    public $daily_recharge_count;//日充值笔数
    public $activity_arpu;//活跃ARPU
    public $pay_arpu;//付费ARPU
    public $pay_rate;//付费率
    public $current_online;//当前在线用户数
    public $max_online;//最大在线用户数
}
