<?php

namespace app\admin\controller;

use think\Db;
use app\common\ServerManage;

class Currency extends Base
{
    /**
     * 货币（钻石、金币、元宝）流向列表
     */
    public function index()
    {
        /** @var 服务器列表 $serverlist */
        $serverlist = ServerManage::getServerList();

        $date = trim(input('date'));
        if ($date) {
            $start = $date . " " . "00:00:00";
            $end = $date . " " . "23:59:59";
        } else {
            $start = date("Y-m-d") . " " . "00:00:00";
            $end = date("Y-m-d") . " " . "23:59:59";
        }
        $filed = "logid,serverId,serverName,userId,playerName,moduleName,value,actionId,logtime";

        $tablename = !empty($date) ? "log" . date('Ymd', strtotime($date)) : "log" . date("Ymd");

        $where[] = ['1', '=', '1'];
        $server_id = trim(input('server_id'));

        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }
        if ($server_id) {
            $where[] = ['serverId', '=', $server_id];
        }
        $action_id = trim(input('action_id'));

        if ($action_id) {
            $where[] = ['actionId', '=', $action_id];
        }

        $player_name = trim(input('playerName'));
        if ($player_name) {
            $where[] = ['playerName', 'like', "%$player_name%"];
        }

        $lists = Db::connect('db_config_log')
            ->table($tablename)
            ->field($filed)
            ->where($where)
            ->where([
                'actionId' => [3, 4, 5, 6, 7, 8]
            ])
            ->whereTime('logtime', [strtotime($start), strtotime($end)])
            ->order('logtime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'date' => $date,
            'playerName' => $player_name,
            'action_id' => $action_id,
            'serverlist' => $serverlist,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '道具流向列表'
        ]);

        return $this->fetch();
    }

}
