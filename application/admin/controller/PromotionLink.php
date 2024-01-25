<?php

namespace app\admin\controller;

use app\admin\model\PropCsv;

class PromotionLink extends Base
{
    /**
     * 系统充值列表
     */
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('player_name'));
        $where[] = ['1', '=', 1];
        if ($player_name) {
            $where[] = ['player_name', 'like', "%$player_name%"];
        }
        
        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        
        $add_date = trim(input('add_date'));
        if ($add_date) {
            $start = strtotime($add_date . " " . "00:00:00");
            $end = strtotime($add_date . " " . "23:59:59");
            $where[] = ['create_time', 'between', [$start, $end]];
        }
    }
}
?>