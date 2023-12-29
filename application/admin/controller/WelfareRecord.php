<?php

namespace app\admin\controller;

use app\admin\model\WelfareRecord as WelfareModel;

use think\facade\View;

class WelfareRecord extends Base
{
    /**
     * 用户福利领取记录
     */
    public function index()
    {
        $player_name = trim(input('player_name'));
        $where[] = ['status', '>', 0];
        if ($player_name) {
            $where[] = ['player_name', 'like', "%$player_name%"];
        }
        $lists = WelfareModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'player_name' => $player_name,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'page' => $page,
            'meta_title' => '用户福利领取列表',
        ]);
        return View::fetch();
    }

    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $ret = \app\admin\model\WelfareRecord::where($where)->update($data);
        if ($ret) {
            action_log('welfare_record_del', 'welfare_record', $ids, UID);
            $this->success('福利领取记录删除成功!');
        } else {
            $this->error('福利领取记录删除失败!');
        }
    }
}
