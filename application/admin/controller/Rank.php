<?php

namespace app\admin\controller;

use app\admin\model\UserChannel;
use app\common\ServerManage;
use think\Db;
use think\facade\Log;
use think\facade\View;
use think\Model;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组

/**
 * 排行
 */
class Rank extends Base
{
    /** 充值排行
     *  读取充值表数据（cq_main.recharge_data）
     */
    public function index()
    {
        //玩家角色id搜索
        $user_id = trim(input('user_id'));

        //筛选排行充值金额
        $money = trim(input('money'));
        if (!isset($money) || empty($money)) $money = 0;

        //筛选排行数量
        $user_count = trim(input('user_count'));
        if (!isset($user_count) || empty($user_count))
            $user_count = 50;//默认显示排行前50名

        $start_time = trim(input('start_time'));
        if (!isset($start_time) || empty($start_time))
            $start_time = date("Y-m-d H:i:s", strtotime("-1 month"));

        $end_time = trim(input('end_time'));
        if (!isset($end_time) || empty($end_time))
            $end_time = date('Y-m-d H:i:s');

        /** 特殊处理（用于公会推广筛选过滤显示数据） **/
        $is_guild = false;
        $ids = '';
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

//            $belong_channel = UserChannel::where('uid', '=', UID)->value('channel_ids');
//            $channel_list = \app\admin\model\Channel::where(['id', 'in', $belong_channel])->select();
        } else {
            $server_list = ServerManage::getServerList();
        }
        $channel_list = \app\admin\model\Channel::select();

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            if ($is_guild) {
                $server_ids = $ids;
            } else {
                $server_ids = explode(',', $server_id);
            }
        }

        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));

        $where[] = ['add_time', 'between', [$start_time, $end_time]];
        $where_str = " r.server_id = s.id  and add_time BETWEEN '" . $start_time . "' AND '" . $end_time . "' ";

        // 混服组特殊处理 START
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $where[] = ['r.channel_id', 'in', $channel_ids];
            $where_str .= " and r.channel_id in  (" . $channel_ids . ")";
        }
        // 混服组特殊处理 END

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where[] = ['s.id', 'between', [$start_server_id, $end_server_id]];
            $search = true;
            $where_str .= " and s.id between " . $start_server_id . " and " . $end_server_id . " ";
        }

        //search==false 排除选择了服务器区间条件
        if ($search == false && !empty($server_ids)) {
            $where[] = ['s.id', 'in', $server_ids];
            $where_str .= " and s.id in (" . $server_id . ") ";
        }

        if (empty($server_ids) && $is_guild == true) {
            $where[] = ['s.id', 'in', rtrim($temp_server_ids, ",")];
            $where_str .= " and s.id in (" . rtrim($temp_server_ids, ",") . ") ";
        }

        $channel_id = trim(input('channel_id'));
        if ($channel_id) {
            $where[] = ['channel_id', '=', $channel_id];
        }

        /***
         * SELECT CONCAT(s.area_id,'-',s.servername) as area_server, r.user_id,r.server_id,SUM(r.money) as total_money,
         * COUNT(r.user_id) as recharge_count,r.add_time from recharge_data r,server_list s WHERE r.server_id=s.id
         * GROUP BY r.user_id HAVING SUM(r.money) ORDER BY total_money desc LIMIT 20 ;
         */

        $field = 's.area_id,s.servername,r.user_id,r.server_id,r.channel_id,s.real_server_id,sum(r.money) as total_money,count(r.user_id) as recharge_count';

        $sql_str = "select sum(total_money) as 'total_money',sum(recharge_count) as 'recharge_count' 
from(SELECT s.area_id,s.servername,r.user_id,r.server_id,sum(r.money) as total_money,count(user_id) as recharge_count from recharge_data r,server_list s WHERE $where_str 
group by r.user_id order by total_money  desc limit " . $user_count . " ) as t";


        $Model = new \app\admin\model\RechargeData();
        $info = $Model->query($sql_str);
        $total_money = 0;
        $recharge_count = 0;
        if (count($info) > 0) {
            $total_money = $info[0]['total_money'];
            $recharge_count = $info[0]['recharge_count'];
        }

        $lists = \app\admin\model\RechargeData::alias('r')
            ->join('server_list s', 'r.server_id=s.id')
            ->field($field)
            ->where($where)
            ->group('r.user_id')
            ->having('sum(r.money) >' . $money)
            ->order('total_money desc,add_time desc')
            ->limit($user_count)
            ->select();

//        $this->ifPageNoData($lists);
//        $page = $lists->render();


        $this->assign([
            'total_money' => $total_money,
            'recharge_count' => $recharge_count,
            'user_id' => $user_id,
            'money' => $money,
            'user_count' => $user_count,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'server_id' => $server_ids,
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'server_list' => $server_list,
            'channel_id' => $channel_id,
            'channel_list' => $channel_list,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="12">暂无数据</td>',
            'meta_title' => '玩家充值排行列表'
        ]);
        return View::fetch();
    }
}
