<?php

namespace app\admin\controller;


use app\admin\model\ServerList;
use app\admin\model\CombineServerRecord as CombineServerRecordModels;
use app\common\ServerManage;
use think\facade\Request;

class CombineServerRecord extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        $where[] = ['1', '=', 1];
        if (!empty($server_id)) {
            $where[] = ['main_server', '=', $server_id];
        }

        $combine_date = trim(input('combine_date'));
        if (!empty($combine_date)) {
            $start = strtotime($combine_date . " " . "00:00:00");
            $end = strtotime($combine_date . " " . "23:59:59");
            $where[] = ['combine_time', 'between', [$start, $end]];
        }


        $lists = CombineServerRecordModels::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'combine_date' => $combine_date,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '合服记录列表信息'
        ]);
        return $this->fetch();
    }

    /**
     * 清空合服记录
     */
    public function clear()
    {
        $res = CombineServerRecordModels::where('1=1')->delete(true);
        if ($res != false) {
            action_log("combine_server_record_clear", "combine_server_record", $res, UID, '清空合服记录数据！！！');
            $this->success('合服记录清空成功！', '');
        } else {
            $this->error('合服记录清空失败！');
        }
    }
}
