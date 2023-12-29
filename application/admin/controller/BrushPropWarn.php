<?php

namespace app\admin\controller;

use \app\admin\model\BrushPropWarn as BrushPropWarnModel;
use app\common\ServerManage;
use think\Db;
use think\facade\Log;
use think\facade\View;

/**
 * 刷道具预警
 */
class BrushPropWarn extends Base
{

    public function index()
    {
        /** 每日刷元宝上限50000 预警 **/

        $actor_id = trim(input('search'));
        $where[] = ['1', '=', 1];

        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        $player_name = trim(input('player_name'));
        if ($player_name) {
            $where[] = ['player', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        $server_list = ServerManage::getServerList();

        $lists = BrushPropWarnModel::where($where)
            ->order('create_time desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'actor_id' => $actor_id,
            'player_name' => $player_name,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '每日刷元宝上限预警'
        ]);
        return View::fetch();
    }

    /**
     * 扫描日志表
     */
    public function scan_log()
    {
        $table_name = "log" . date('Ymd', time());
        $where[] = [
            ['actionId', '=', 8],
            ['moduleId', '<>', 1],
            ['moduleId', '<>', 0],
        ];

        $ret_arr = array();
        $log_list = Db::connect('db_config_log')
            ->table($table_name)
            ->field('serverId,serverName,userId,playerName,sum(value) as total')
            ->where($where)
            ->group('userId')
            ->having('sum(value)> 50000')
            ->order('total desc')
            ->select();
        $count = count($log_list);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $info = $log_list[$i];
                $data['actor_id'] = $info['userId'];
                $data['player_name'] = $info['playerName'];
                $data['server_id'] = $info['serverId'];
                $data['server_name'] = $info['serverName'];
                $data['prop_type'] = 103;//元宝
                $data['prop_value'] = $info['total'];
                $data['create_time'] = time();
                $checkInfo =  BrushPropWarnModel::where([['actor_id','=',$info['userId']]])->find();
                if($checkInfo)
                {
                    BrushPropWarnModel::where([['actor_id','=',$info['userId']]])->delete();
                }
                array_push($ret_arr, $data);
            }
            $result = BrushPropWarnModel::insertAll($ret_arr);
            if (!$result) {
                Log::write("非直充金额超上限预警数据记录失败!!!");
                echo "fail";
            } else {
                echo "success";
            }
        } else {
            echo "暂无上限预警数据！！！";
        }

    }
}
