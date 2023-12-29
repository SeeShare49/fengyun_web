<?php

namespace app\admin\controller;

use app\admin\model\ActionLog as LogModel;

class ActionLog extends Base
{
    /**
     * 用户行为日志列表
     */
    public function index()
    {
        $title = trim(input('title'));
        $this->assign('title', $title);

        $map['a.status'] = array('gt', '0');
        $lists = LogModel::alias('a')
            ->join('action b', 'a.action_id = b.id')
            ->join('users c', 'a.user_id = c.id', 'LEFT')
            ->field('a.*,b.title,c.nickname')
            ->order('a.id desc');

        if ($title) {
            $lists = $lists->where('b.title', 'like', "%$title%");
        }
        $lists = $lists->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'page'=>$page,
            'lists'=>$lists,
            'meta_title'=>'用户行为日志列表'
        ]);
        return $this->fetch();
    }

    /**
     * 行为日志详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function action_detail($id)
    {
        $info = LogModel::alias('a')
            ->join('action b', 'a.action_id = b.id')
            ->join('users c', 'a.user_id = c.id', 'LEFT')
            ->field('a.*,b.title,c.nickname')
            ->find($id);
        if (!$info) {
            $this->error('行为记录不存在或已删除！');
        }
        $this->assign([
            'id'=>$id,
            'info'=>$info,
            'meta_title'=>'行为日志详细'
        ]);
        return $this->fetch();
    }

    /**
     * 删除日志
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];

        $res = LogModel::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("actionlog_del", "action_log", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 清空日志
     */
    public function clear()
    {
        $res = LogModel::where('1=1')->delete();
        if ($res !== false) {
            $this->success('日志清空成功！', '');
        } else {
            $this->error('日志清空失败！');
        }
    }
}
