<?php

namespace app\admin\controller;

use app\common\ServerManage;
use think\facade\Request;
use think\facade\View;
use app\common\test;

class ActivityTime extends Base
{
    public function index()
    {
        $where[] = ['1', '=', 1];
        /** 服务器列表 **/
        $server_list = ServerManage::getServerList();

        $activity_id = trim(input('activity_id'));
        if ($activity_id) {
            $where[] = ['activity_id', '=', $activity_id];
        }

        $server_id = trim(input('server_id'));
        /** server_id为空  默认选择服务器列表中的第一条符合条件的服务器ID **/
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }
        $lists = dbConfig($server_id)
            ->table('activity_time')
            ->where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'page' => $page,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'meta_title' => '游戏活动信息'
        ]);
        return View::fetch();
    }

    /**
     * 管理后台发送活动信息到服务器
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $server_id = $data['server'];
            $op_code = $data['op_code'];//1 加载所有活动 2 加载单个活动 3加载单个函数 （服务端确定选择值）
            $activity = array();
            $activity['activity_id'] = $data['activity_id'];
            $activity['time_type'] = $data['time_type'];
            $activity['date'] = $data['date'];
            $activity['time_param'] = $data['time_param'];
            $activity['func_name'] = $data['func_name'];
            $activity['param1'] = $data['param1'];
            $activity['param2'] = $data['param2'];
            $activity['param3'] = $data['param3'];
            $ret = dbConfig($server_id)->table('activity_time')->insert($activity);
            if ($ret) {
                action_log("activity_time_add", "activity_time", $ret, UID);
                /** 活动配置发送服务器通知 **/
                test::webw_packet_config_activity($server_id, $activity['activity_id'], $op_code, $activity['func_name']);
                $this->success("游戏活动信息添加成功!", "activity_time/index");
            } else {
                $this->error("游戏活动信息添加失败!");
            }
        } else {
            $server_list = ServerManage::getServerList();
            View::assign([
                'server_list' => $server_list,
                'meta_title' => '添加游戏活动信息'
            ]);
            return View::fetch();
        }
    }
}
