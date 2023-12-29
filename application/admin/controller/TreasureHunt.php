<?php

namespace app\admin\controller;


use app\common\ServerManage;
use think\facade\Log;
use think\facade\View;

class TreasureHunt extends Base
{
    /**
     * 寻宝排行列表
     */
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

        //寻宝类型
        $where[] = ['limit_type', '=', 8];

        /**
         * 角色ID
         **/
        $actor_id = trim(input('actor_id'));
        if ($actor_id) {
            $where[] = ['t.actor_id', '=', $actor_id];
        }

        /**
         * 账号ID
         */
        $account_id = trim(input('account_id'));
        if ($account_id) {
            $where[] = ['p.account_id', '=', $account_id];
        }

        /**
         * 角色昵称
         */
        $nickname = trim(input('nickname'));
        if ($nickname) {
            $where[] = ['p.nickname', '=', $nickname];
        }

        //筛选排行数量
        $user_count = trim(input('user_count'));
        if (!isset($user_count) || empty($user_count))
            $user_count = 200;//默认显示排行前50名

        $lists = dbConfig($server_id)
            ->table('all_time_limit')
            ->alias('t')
            ->join('player p', 'p.actor_id=t.actor_id')
            ->field('t.actor_id,t.limit_id,t.limit_value,p.account_id,p.nickname')
            ->where($where)
            ->order('t.limit_value desc')
            ->limit($user_count)
            ->select();

        $this->assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'actor_id' => $actor_id,
            'lists' => $lists,
            'user_count' => $user_count,
            'empty'=>'<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '寻宝排行榜'
        ]);
        return $this->fetch();
    }
}
