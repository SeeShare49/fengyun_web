<?php

namespace app\admin\controller;

use app\admin\model\UserChannel;
use app\common\GameLogActionManage;
use app\common\GameLogActionType;
use app\common\ServerManage;

use app\common\test;
use page\Page;
use think\Db;
use think\facade\Log;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组


class Prop extends Base
{
    /**
     * 道具流向列表(包括添加道具、消耗道具、元宝消耗)
     */

    public function index()
    {
        /**
         * 混服组特殊处理
         **/
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $where_str = " channel_id in  (" . $channel_ids . ")";
        } else {
            $where_str = "1=1 ";
        }

        $ids = '';
        $is_guild = false;
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
        $action_lists = GameLogActionType::GetActionTypeListByIds();

        $prop_flow_scene = GameLogActionManage::getPropUseScene();//道具日志行为类型

        $start_date = trim(input('start_date'));
        $end_date = trim(input('end_date'));
        $filed = "logid,serverId,serverName,userId,playerName,moduleId,moduleName,`value`,actionName,remark,logtime,guid";
        $table_name = "Log" . date("Ymd"); //初始默认查询当日数据表
        $exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        if (!$exists_table) {
            $this->error("数据表【{$table_name}】不存在！！！！");
        }

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if ($server_id && $server_id != -1) {
            if ($is_guild) {
                $server_ids = $ids;
            } else {
                $server_ids = explode(',', $server_id);
                $where_str .= " and serverId in (" . $server_id . ")";
            }
        }

        if (empty($server_ids) && $is_guild == true) {
            $where_str .= " and serverId in (" . rtrim($temp_server_ids, ",") . ")";
        }

        $module_id = trim(input('module_id'));
        if ($module_id == "0") {
            $where_str .= ' and moduleId=0';
        }
        if ($module_id) {
            $where_str .= ' and moduleId=' . $module_id;
        }

        //道具ID
        $prop_value = trim(input('prop_value'));
        if ($prop_value) {
            $where_str .= ' and value=' . $prop_value;
        }

        $action_id = trim(input('action_id'));
        $action_ids = '';
        if ($action_id) {
            $action_ids = explode(',', $action_id);
            $where_str .= " and actionId in (" . $action_id . ")";
        }
//        } else {
//            //$action_id = "2,3,8,9";
//            //$action_ids = explode(',', $action_id);
//            $where_str .= ' and actionId in (2,3,8,9)';
//        }

        $user_id = trim(input('userId'));
        if ($user_id) {
            $where_str .= ' and userId=' . $user_id;
        }

        //追踪ID
        $guid = trim(input('guid'));
        if ($guid) {
            $where_str .= ' and guid=' . $guid;
        }

        $player_name = trim(input('playerName'));
        if ($player_name) {
            $where_str .= " and playerName like '%" . $player_name . "%'";
        }
        $lists = '';
        $lists_sql = '';
        $total = 0;
        $curr_page = input('page/d', 1);
        if (isset($start_date) && !empty($start_date)) {
            //开始日期不为空
            if (isset($end_date) && !empty($end_date)) {
                if ($start_date == $end_date) {
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                } else if ($start_date < $end_date) {
                    // TODO:开始日期小于等于结束日期
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                    $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
                    if ($diff > 0) {
                        for ($i = 1; $i <= $diff; $i++) {
                            $union_table = 'Log' . date("Ymd", strtotime("+$i day", strtotime($start_date)));
                            $lists_sql .= 'union all select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . ' ';
                        }
                    } else {
                        $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                    }
                } else {
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                }
            } else {
                $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
            }
        } else {
            $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . '  ';
        }


        $total = count(Db::connect('db_config_log_read')->query($lists_sql));//统计数据总数
        $lists_sql .= ' order by logtime desc limit ' . ($curr_page - 1) * config('LIST_ROWS') . ',' . config('LIST_ROWS') . '';
        //$lists = Db::connect('db_config_log_read')->query($lists_sql, [($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS')]);
        $lists = Db::connect('db_config_log_read')->query($lists_sql);
        $pagernator = Page::make($lists, config('LIST_ROWS'), $curr_page, $total, false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
        $page = $pagernator->render();

        $this->assign([
            'server_id' => $server_ids,
            'module_id' => $module_id,
            'action_id' => $action_ids,
            'prop_value' => $prop_value,
            'action_lists' => $action_lists,
            'playerName' => $player_name,
            'start_date' => $start_date ? $start_date : date('Y-m-d'),
            'end_date' => $end_date ? $end_date : date('Y-m-d'),
            'user_id' => $user_id,
            'guid' => $guid,
            'server_list' => $server_list,
            'prop_flow_scene' => $prop_flow_scene,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '道具流向列表'
        ]);
        return $this->fetch();
    }

    /**
     * 删除玩家身上道具
     * @param $server_id
     * @param $actor_id
     * @param $guid
     * @throws \think\Exception
     */
    public function del($server_id, $actor_id, $guid)
    {
        if ($server_id && $actor_id && $guid) {
            $info = dbConfigByReadBase($server_id)->table('player')->where('actor_id', '=', $actor_id)->find();
            if ($info) {
                $checkInfo = dbConfigByReadBase($server_id)->table('player_item')->where([['ident_id', '=', $guid], ['actor_id', '=', $actor_id]])->find();
                $itemInfo = dbConfig($server_id)->table('player_item')->where('ident_id', '=', $guid)->delete();
                if ($itemInfo) {
                    $deduct['server_id'] = $server_id;
                    $deduct['user_id'] = $info['account_id'];
                    $deduct['actor_id'] = $actor_id;
                    $deduct['nick_name'] = $info['nickname'];
                    $deduct['prop_type'] = $checkInfo['type_id'];
                    $deduct['ingot'] = $checkInfo['number'];
                    $deduct['operator'] = USERNAME;
                    $deduct['create_time'] = time();
                    \app\admin\model\DeductIngot::insert($deduct);

                    $data['BanReason'] = "";
                    test::webw_packet_ban_user($info['account_id'], 0, "");
                    $this->success("跟踪ID:【" . $guid . "】道具数据信息删除成功,待服务器处理......");
                } else {
                    $this->error("跟踪ID:【" . $guid . "】道具数据信息删除失败!!!");
                }
            } else {
                $this->error("服务器ID:【" . $server_id . "】不存在角色ID:【" . $actor_id . "】玩家信息!!!");
            }
        }
    }
}
