<?php

namespace app\admin\controller;

use app\admin\validate\Task as TaskValidate;

use app\admin\model\Task as TaskModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Task extends Base
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

        $this->assign('name', $name);
        $lists = TaskModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign('lists', $lists);
        $this->assign('page', $page);
        $this->meta_title = '任务分类信息';
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
            $taskValidate = new TaskValidate();
            if (!$taskValidate->check($data)) {
                $this->error($taskValidate->getError());
            }
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkName = TaskModel::where($checkWhere)->find();

            if ($checkName) {
                $this->error("{$data['name']}任务分类名称已存在,请勿重复添加!");
            }

            $re = TaskModel::insertGetId($data);
            if ($re) {
                action_log('task_add', 'task', $re, UID);
                $this->success("{$data['name']}活动任务分类添加成功!", 'task/index');
            } else {
                $this->error("{$data['name']}活动任务分类添加失败!");
            }
        } else {
            $this->meta_title = "添加活动任务分类";
            return $this->fetch();
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
        $info = TaskModel::find($id);
        if (!$info) {
            $this->error("活动任务分类不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $taskValidate = new TaskValidate();
            if (!$taskValidate->check($data)) {
                $this->error($taskValidate->getError());
            }
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            try {
                $checkName = TaskModel::where($checkWhere)->find();

                if ($checkName) {
                    $this->error("{$data['name']}任务分类名称已存在,请重新编辑!");
                }
            } catch (DataNotFoundException $e) {
            } catch (ModelNotFoundException $e) {
            } catch (DbException $e) {
            }

            $re = TaskModel::update($data);
            if ($re) {
                action_log('task_edit', 'task', $re, UID);
                $this->success("{$data['name']}活动任务分类编辑成功!", "task/index");
            } else {
                $this->error("{$data['name']}活动任务分类编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '添加活动任务分类'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除指定资源
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = TaskModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("task_del", "task", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }


    /**
     * 显示/隐藏任务类型
     */
    public function set_type_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $task_status = "task_status_show";
            if ($data['status'] == 0) $task_status = "task_status_hide";

            $res = TaskModel::update($data);
            if ($res) {
                //添加行为记录
                if (isset($task_status)) {
                    action_log($task_status, "task", $data['id'], UID);
                }
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
