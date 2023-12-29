<?php

namespace app\admin\controller;

use app\common\ServerManage;
use think\facade\View;

/**
 * 元宝排行
 */
class IngotRank extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));

        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        $where[] = ['1', '=', 1];
        /**
         * 角色ID
         **/
        $actor_id = trim(input('actor_id'));
        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        $account_id = trim(input('account_id'));
        if ($account_id) {
            $where[] = ['account_id', '=', $account_id];
        }

        $player_name = trim(input('player_name'));
        if ($player_name) {
            $where[] = ['nickname', 'like', "%$player_name%"];
        }

        //筛选排行数量
        $user_count = trim(input('user_count'));
        if (!isset($user_count) || empty($user_count))
            $user_count = 200;//默认显示排行前50名

        $lists = dbConfigByReadBase($server_id)
            ->table('player')
            ->field('actor_id,account_id,nickname,level,job,gender,yuanbao,gold,diamonds')
            ->where($where)
            ->order('yuanbao desc')
            ->limit($user_count)
            ->select();

        View::assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'actor_id' => $actor_id,
            'player_name' => $player_name,
            'lists' => $lists,
            'user_count' => $user_count,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '元宝排行榜'
        ]);
        return View::fetch();
    }
}
