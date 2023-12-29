<?php

namespace app\admin\controller;

use app\common\ServerManage;
use think\Db;

/**
 * 扣除道具记录列表
 */
class DeductIngot extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();

        $where[] = ['1', '=', '1'];
        $server_id = trim(input('server_id'));

        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        $user_id = trim(input('action_id'));

        if ($user_id) {
            $where[] = ['user_id', '=', $user_id];
        }

        $actor_id = trim(input('actor_id'));

        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        $nick_name = trim(input('nick_name'));
        if ($nick_name) {
            $where[] = ['nick_name', 'like', "%$nick_name%"];
        }

        $lists = \app\admin\model\DeductIngot::where($where)
            ->order('create_time desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'user_id' => $user_id,
            'actor_id' => $actor_id,
            'nick_name' => $nick_name,
            'server_list' => $server_list,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="9">暂无数据</td>',
            'meta_title' => '道具扣除记录列表'
        ]);

        return $this->fetch();
    }
}
