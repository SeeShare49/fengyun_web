<?php

namespace app\admin\controller;

use app\common\ServerManage;
use think\Db;
use think\facade\View;

/**
 * 游戏界面行为统计
 **/
class InterfaceAction extends Base
{
    public function index()
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
        $table_name = !empty($date) ? "Log" . date('Ymd', strtotime($date)) : "Log" . date("Ymd");
        $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        if (!$exists_table) {
            $this->error("数据表【{$table_name}】不存在！！！！");
        }
        $filed = " logtime,serverId,serverName,moduleId,actionId,value,remark,channel_id,count(1) as records";
        $where[] = [['moduleId', '=', 107], ['actionId', 'in', [100, 101]], ['logtime', 'between', [$start, $end]]];


        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
        }

        $search = false;
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
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
        //serverId大于等于10000所属跨服不计入
        $where[] = ['serverId', '<', 10000];

        $total = Db::connect('db_config_log_read')->table($table_name)->where($where)->count();

        //功能类别（充值、活动......）
        $function_type = trim(input('function_type'));
        if ($function_type && $function_type != -1) {
            $where[] = ['actionId', '=', $function_type];
        }

        $lists = Db::connect('db_config_log_read')
            ->table($table_name)
            ->field($filed)
            ->where($where)
            ->group('actionId,value,remark')
            ->order('serverId desc,logtime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'date' => $date,
            'server_id' => $server_ids,
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'server_list' => $server_list,
            'lists' => $lists,
            'total' => $total,
            'page' => $page,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '游戏界面行为统计'
        ]);
        return View::fetch();
    }
}
