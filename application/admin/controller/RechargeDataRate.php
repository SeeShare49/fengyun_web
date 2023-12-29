<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\admin\model\UserChannel;
use app\common\ChannelManage;
use app\common\ServerManage;
use app\common\test;
use think\facade\Log;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组

class RechargeDataRate extends Base
{
    public function index()
    {
        /**
         * 混服组特殊处理
         **/
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $channel_list = ChannelManage::getChannelListByIds($channel_ids);
            $where[] = ['channel_id', 'in', $channel_ids];
        } else {
            $channel_list = ChannelManage::getChannelList();
            $where[] = ['1', '=', 1];
        }

        $is_guild = false;
        $ids = '';
        $temp_server_ids = '';
        $server_list = ServerManage::getServerList();
        $this->assign('serverlist', $server_list);


        $user_id = trim(input('user_id'));

        if ($user_id) {
            $where[] = ['user_id', '=', $user_id];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            if ($is_guild) {
                $where[] = ['server_id', '=', rtrim($temp_server_ids, ",")];
            } else {
                $where[] = ['server_id', '=', $server_id];
            }
        }

        if (empty($server_ids) && $is_guild == true) {
            $where[] = ['server_id', '=', rtrim($temp_server_ids, ",")];
        }

        $channel_id = trim(input('channel_id'));
        if ($channel_id) {
            $where[] = ['channel_id', '=', $channel_id];
        }

        $order_status = trim(input('order_status'));
        if ($order_status == 100) {
            $where[] = ['order_status', '=', 0];
        } else if ($order_status == 2) {
            $where[] = ['order_status', '=', 2];
        } else {
            $order_status = 1;
            $where[] = ['order_status', '=', 1];
        }

        //是否对账（财务对账状态）
        $is_check = trim(input('is_check'));
        if ($is_check == 100) {
            $where[] = ['is_check', '=', 0];
        } elseif ($is_check == 1) {
            $where[] = ['is_check', '=', $is_check];
        }

        //支付方式
        $pay_type = trim(input('pay_type'));
        if ($pay_type && $pay_type != 0) {
            $where[] = ['pay_type', '=', $pay_type];
        }

        $amount = trim(input('amount'));
        if ($amount && $amount != -1) {
            $where[] = ['money', '=', $amount];
        }

        //订单编号
        $order_id = trim(input('order_id'));
        if ($order_id) {
            $where[] = ['order_id', '=', $order_id];
        }

        //起始时间查询
        $start_date = trim(input('start_date'));
        if ($start_date) {
            //$start = $start_date . " " . "00:00:00";
            $where[] = ['add_time', '>=', $start_date];
        }
        //结束时间查询
        $end_date = trim(input('end_date'));
        if ($end_date) {
            //$end = $end_date . " " . "23:59:59";
            $where[] = ['add_time', '<=', $end_date];
        }

        //统计充值总额
        $total_money = 0;
        $rechargeInfo = \app\pay\model\RechargeData::field('sum(money) as total')->where($where)->where('order_status=1')->find();
        if ($rechargeInfo) {
            $total_money = $rechargeInfo['total'];
        }

        $lists = \app\pay\model\RechargeData::field('id,server_id,user_id,money,amount,order_id,pay_ip,order_status,channel_id,is_check,pay_type,add_time')->where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'amount' => $amount,
            'total_money' => $total_money,
            'user_id' => $user_id,
            'server_id' => $server_id,
            'lists' => $lists,
            'is_check' => $is_check,
            'order_status' => $order_status,
            'order_id' => $order_id,
            'channel_list' => $channel_list,
            'channel_id' => $channel_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pay_type' => $pay_type,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'page' => $page,
            'meta_title' => '系统充值列表'
        ]);
        return $this->fetch();
    }
}
