<?php

namespace app\admin\controller;

use app\common\ServerManage;
use app\admin\model\ActivityServerName as ActivityServerNameModel;
use think\facade\View;

define('GROUP_ID', config('admin.GROUP_ID'));

class ActivityServerName extends Base
{
    /**
     * 服务器冠名列表
     */
    public function index()
    {
        /** 特殊处理（用于公会推广筛选过滤显示数据） **/

        if (GROUPID == GROUP_ID) {
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

        $actor_id = trim(input('actor_id'));
        $actor_name = trim(input('actor_name'));
        $server_id = trim(input('server_id'));

        $where[] = ['1', '=', 1];
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        if ($actor_name) {
            $where[] = ['actor_name', 'like', "%$actor_name%"];
        }

        $lists = dbConfig($server_id)
            ->table('activity_server_name')
            ->field('actor_id,actor_name,total_value,change_time,job,gender')
            ->where($where)
            ->order('total_value desc,change_time asc')
            ->select();

        View::assign([
            'server_id' => $server_id,
            'actor_id' => $actor_id,
            'actor_name' => $actor_name,
            'server_list' => $server_list,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '服务器冠名列表'
        ]);

        return View::fetch();
    }
}
