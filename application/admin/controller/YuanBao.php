<?php

namespace app\admin\controller;

use app\admin\model\UserChannel;
use app\common\GameLogActionManage;
use app\common\ServerManage;
use page\Page;
use think\Db;
use think\facade\Log;
use think\facade\View;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组

class YuanBao extends Base
{
    /**
     * 元宝消耗列表
     **/

    public function index()
    {
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
        $prop_flow_scene = GameLogActionManage::getPropUseScene();//道具日志行为类型
        $table_name = "Log" . date("Ymd"); //默认查询当天数据
        $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        if (!$exists_table) {
            $this->error("数据表【{$table_name}】不存在！！！！");
        }
        $filed = "serverId,moduleId,sum(value) as total,logtime";

        // 混服组特殊处理 START
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $where_str = " channel_id in  (" . $channel_ids . ")";
        } else {
            $where_str = " 1=1 ";
        }
        // 混服组特殊处理 END

        $start_date = trim(input('start_date'));
        $end_date = trim(input('end_date'));

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if ($server_id && $server_id != -1) {
            if ($is_guild) {
                $server_ids = $ids;
                $where_str .= " and serverId in (" . rtrim($temp_server_ids, ",") . ")";
            } else {
                $server_ids = explode(',', $server_id);
                $where_str .= " and serverId in (" . $server_id . ")";
            }
        }

        if (empty($server_ids) && $is_guild == true) {
            $where_str .= " and serverId in (" . rtrim($temp_server_ids, ",") . ") ";
        }

        $module_id = trim(input('module_id'));
        $module_ids = '';
        if ($module_id) {
            $module_ids = explode(',', $module_id);
            $where_str .= " and moduleId in(" . $module_id . ")";
        }

        $action_id = trim(input('action_id'));
        if ($action_id && $action_id != -1) {
            $where_str .= " and actionId=" . $action_id;
        } else {
            $action_id = -1;
            $where_str .= " and actionId in (8, 9)";//添加元宝、消耗元宝
        }

        $lists = '';
        $lists_sql = '';
        $curr_page = input('page/d', 1);
        $totalValue = 0;
        if (isset($start_date) && !empty($start_date)) {
            //开始日期不为空
            if (isset($end_date) && !empty($end_date)) {
                if ($start_date == $end_date) {
                    $table_name = " Log" . date('Ymd', strtotime($start_date));
                    $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
                    //判断数据表是否存在 TODO:by sgy update 2021-07-19
                    if ($exists_table) {
                        $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' group by moduleId ';
                        $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from log' . date('Ymd', strtotime($start_date)) . ' where ' . $where_str);
                    } else {
                        $table_name = " Log" . date('Ymd', strtotime("-1 day", strtotime($start_date)));
                        $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' group by moduleId ';
                        $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from log' . date('Ymd', strtotime("-1 day", strtotime($start_date))) . ' where ' . $where_str);
                    }
                } else if ($start_date < $end_date) {
                    //TODO:开始日期小于等于结束日期

                    //多表查询特殊处理
                    $union_field = " serverId,moduleId,value,logtime ";
                    $lists_sql = "select " . $filed . " from ( ";

                    $lists_sql .= 'select ' . $union_field . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . '  ';
                    $total_value_sql = 'select sum(value) as value from (select sum(value) as value from Log' . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                    $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
                    if ($diff > 0) {
                        for ($i = 1; $i <= $diff; $i++) {
                            $union_table = 'Log' . date("Ymd", strtotime("+$i day", strtotime($start_date)));
                            $lists_sql .= 'union all select ' . $union_field . ' from ' . $union_table . ' where ' . $where_str . ' ';
                            $total_value_sql .= 'union all select sum(value) as value from ' . $union_table . ' where ' . $where_str . ' ';
                        }
                        $total_value_sql .= ') as t';
                        $lists_sql .= " ) as tt group by moduleId ";

                        $totalValue = Db::connect('db_config_log')->query($total_value_sql);
                    } else {
                        $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' group by moduleId ';
                        $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from Log' . date('Ymd', strtotime($start_date)) . ' where ' . $where_str);
                    }
                } else {
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' group by moduleId ';
                    $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from Log' . date('Ymd', strtotime($start_date)) . ' where ' . $where_str);
                }
            } else {
                $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' group by moduleId ';
                $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from Log' . date('Ymd', strtotime($start_date)) . ' where ' . $where_str);
            }
        } else {
            $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' group by moduleId ';
            $totalValue = Db::connect('db_config_log')->query('select sum(value) as value from ' . $table_name . ' where ' . $where_str);
        }
        $total = count(Db::connect('db_config_log')->query($lists_sql));//统计数据总数
        //$lists_sql .= ' order by logtime desc limit ?,?';
        //$lists = Db::connect('db_config_log')->query($lists_sql, [($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS')]);
        $lists_sql .= ' order by logtime desc';
        $lists_sql .= ' limit '.(($curr_page - 1) * intval(config('LIST_ROWS'))).','.intval(config('LIST_ROWS'));
        $lists = Db::connect('db_config_log')->query($lists_sql);
        $pagernator = Page::make($lists, config('LIST_ROWS'), $curr_page, $total, false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
        $page = $pagernator->render();

        View::assign([
            'server_list' => $server_list,
            'prop_flow_scene' => $prop_flow_scene,
            'server_id' => $server_ids,
            'module_id' => $module_ids,
            'action_id' => $action_id,
            'start_date' => $start_date ? $start_date : date('Y-m-d'),
            'end_date' => $end_date ? $end_date : date('Y-m-d'),
            'lists' => $lists,
            'page' => $page,
            'totalValue' => $totalValue[0]['value'],
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '元宝消耗占比统计'
        ]);
        return View::fetch();
    }
}
