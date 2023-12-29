<?php

namespace app\admin\controller;


class FilterControl extends Base
{
    public function index()
    {
        $filter_str = trim(input('filter_str'));
        $where[] = ['1', '=', 1];
        if ($filter_str) {
            $where[] = ['filter_str', 'like', "%$filter_str%"];
        }

        $lists = \app\admin\model\FilterControl::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'filter_str' => $filter_str,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '游戏聊天监控过滤关键字'
        ]);
        return $this->fetch();
    }


    public function edit($id)
    {
        $info = \app\admin\model\FilterControl::find($id);
        if (!$info) {
            $this->error("监控过滤关键字不存在或已删除!");
        }
        if (request()->isPost()) {
            $data = $_POST;
            $validate = new \app\admin\validate\FilterControl();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $re = \app\admin\model\FilterControl::update($data);
            if ($re) {
                action_log("filter_control_edit", "filter_control", $re, UID);
                $this->success('监控过滤关键字编辑成功!', 'filter_control/index');
            } else {
                $this->error("监控过滤关键字编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑监控过滤关键字'
            ]);
            return $this->fetch();
        }
    }
}
