<?php

namespace app\admin\controller;

use app\admin\validate\ActivityType as TypeValidate;

use app\admin\model\ActivityType as TypeModel;
use think\facade\View;

class ActivityType extends Base
{
    /**
     * 活动类别列表
     * @return \think\Response
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $name = trim(input('name'));
        $where[] = ['status', '>', -1];
        if ($name) {
            $where[] = ['name', 'like', "%$name%"];
        }

        $lists = TypeModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'name' => $name,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '活动类别列表'
        ]);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $typeValidate = new TypeValidate();
            if (!$typeValidate->check($data)) {
                $this->error($typeValidate->getError());
            }
            $checkWehre[] = ['name', '=', $data['name']];
            $checkWehre[] = ['status', '>', -1];
            $checkName = TypeModel::where($checkWehre)->find();

            if ($checkName) {
                $this->error("{$data['name']}活动类别已存在,请勿重复添加!");
            }

            $re = TypeModel::insertGetId($data);
            if ($re) {
                action_log('activity_type_add', 'activity_type', $re, UID);
                $this->success("{$data['name']}活动类别添加成功!", 'activity_type/index');
            } else {
                $this->error("{$data['name']}活动类别添加失败!");
            }
        } else {
            View::assign([
                'meta_title' => '添加活动类别'
            ]);
            return View::fetch();
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = TypeModel::find($id);
        if (!$info) {
            $this->error("活动类别不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $typeValidate = new TypeValidate();
            if (!$typeValidate->check($data)) {
                $this->error($typeValidate->getError());
            }
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            $checkName = db('activity_type')
                ->where($checkWhere)
                ->find();
            if ($checkName) {
                $this->error("{$data['name']}活动类别已存在,请重新编辑类别名称!");
            }

            $re = TypeModel::update($data);
            if ($re) {
                action_log('activity_type_edit', 'activity_type', $re, UID);
                $this->success("{$data['name']}活动类别编辑成功!", "activity_type/index");
            } else {
                $this->error("{$data['name']}活动类别编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '添加活动类别'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定资源
     * @return \think\Response
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = TypeModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("activity_type_del", "activity_type", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }


    /**
     * 设置活动分类显示/隐藏
     */
    public function set_type_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $type_status = "activity_type_status_show";
            if ($data['status'] == 0) $type_status = "activity_type_status_hide";

            $res = TypeModel::update($data);
            if ($res) {
                /** 添加行为记录 @var TYPE_NAME $type_status */
                action_log($type_status, "activity_type", $data['id'], UID);
                $this->success('活动分类状态修改成功！');
            } else {
                $this->error('活动分类状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
