<?php

namespace app\admin\controller;

use app\admin\validate\TargetType as TargetTypeValidate;

use app\admin\model\TargetType as TargetTypeModel;

class TargetType extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $name = trim(input('$name'));
        $where[] = ['status', '>', -1];
        if ($name) {
            $where[] = ['name', 'like', "%$name%"];
        }

        $lists = TargetTypeModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'name' => $name,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '活动目标分类信息'
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
            $tpeValidate = new TargetTypeValidate();
            if (!$tpeValidate->check($data)) {
                $this->error($tpeValidate->getError());
            }
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkName = TargetTypeModel::where($checkWhere)
                ->find();

            if ($checkName) {
                $this->error("{$data['name']}活动目标分类名称已存在,请勿重复添加!");
            }

            $re = TargetTypeModel::insertGetId($data);
            if ($re) {
                action_log('target_type_add', 'target_type', $re, UID);
                $this->success("{$data['name']}活动目标分类添加成功!", 'target_type/index');
            } else {
                $this->error("{$data['name']}活动目标分类添加失败!");
            }
        } else {
            $this->assign([
                'meta_title' => '添加活动目标分类'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = db('target_type')->find($id);
        if (!$info) {
            $this->error("活动任务分类不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $taskValidate = new TargetTypeValidate();
            if (!$taskValidate->check($data)) {
                $this->error($taskValidate->getError());
            }
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            $checkName = TargetTypeModel::where($checkWhere)->find();
            if ($checkName) {
                $this->error("{$data['name']}活动目标分类名称已存在,请重新编辑!");
            }

            $re = TargetTypeModel::update($data);
            if ($re) {
                action_log('target_type_edit', 'target_type', $re, UID);
                $this->success("{$data['name']}活动任务目标编辑成功!", "target_type/index");
            } else {
                $this->error("{$data['name']}活动任务目标编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '添加活动目标分类'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除指定资源
     *
     * @param int $id
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
        $res = TargetTypeModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("target_type_del", "target_type", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
}
