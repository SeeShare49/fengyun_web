<?php

namespace app\admin\controller;

use app\common\ServerManage;

define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组
class TopList extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
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

        $where[] = ['1', '=', 1];

        /**
         * 角色ID
         **/
        $actor_id = trim(input('actor_id'));
        if ($actor_id)
        {
            $where[] = ['t.actor_id', '=', $actor_id];
        }

        //筛选排行数量
        $user_count = trim(input('user_count'));
        if (!isset($user_count) || empty($user_count))
        {
            $user_count = 100;//默认显示排行前50名
        }
            
        if (GROUPID == MIX_GROUP_ID)
        {
            $channel_ids =\app\admin\model\UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids) || !isset($channel_ids))
            {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $where[] = ['u.ChannelID', 'in', $channel_ids];
            $lists = dbConfig($server_id)
                ->table('top_list')
                ->alias('t')
                ->join('cq_main.user_info u', 't.actor_id=u.UserID')
                ->field('t.actor_id,t.job,t.gender,t.lv')
                ->where($where)
                ->order('t.lv desc')
                ->limit($user_count)
                ->select();
        } 
        else 
        {
            $lists = dbConfig($server_id)
                ->table('top_list')
                ->alias('t')
                ->join('player p','t.actor_id=p.actor_id')
                ->field('t.actor_id,t.job,t.gender,t.lv,p.nickname')
                ->where($where)
                ->order('t.lv desc')
                ->limit($user_count)
                ->select();
        }
        $this->assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'actor_id' => $actor_id,
            'lists' => $lists,
            'user_count' => $user_count,
            'meta_title' => '等级排行榜'
        ]);
        return $this->fetch();
    }

}
