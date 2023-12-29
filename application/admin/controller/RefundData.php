<?php

namespace app\admin\controller;

use think\facade\View;

/**
 * Apple Pay退款数据
 */
class RefundData extends Base
{
    /**
     * 退款数据记录列表
     */
    public function index()
    {
        $order_id = trim(input('order_id'));
        $trade_no = trim(input('trade_no'));

        $start_date = trim(input('start_date'));
        if (!isset($start_date) || empty($start_date))
            $start_date = date("Y-m-d H:i:s", strtotime("-1 month"));

        $end_date = trim(input('end_date'));
        if (!isset($end_date) || empty($end_date))
            $end_date = date('Y-m-d H:i:s');

        $where[] = ['1', '=', 1];
        if ($order_id) {
            $where[] = ['order_id', '=', $order_id];
        }

        if ($trade_no) {
            $where[] = ['trade_no', '=', $trade_no];
        }

        $lists = \app\admin\model\RefundData::where($where)
            ->order('create_time desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        View::assign([
            'order_id' => $order_id,
            'trade_no' => $trade_no,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'page' => $page,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="9">暂无数据</td>',
            'meta_title' => '退款订单列表'
        ]);
        return View::fetch();
    }
}
