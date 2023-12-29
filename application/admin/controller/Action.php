<?php

namespace app\admin\controller;

use app\admin\validate\Action as ActionValidate;

use app\admin\model\Action as ActionModel;
use think\facade\Request;

class Action extends Base
{
    /**
     * 用户行为列表
     */
    public function index()
    {
        $title = trim(input('title'));
        $where[] = ['status', '>', -1];
        if ($title) {
            $where[] = ['name|title', 'like', "%$title%"];
        }

        $this->assign('title', $title);
        $lists = ActionModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '用户行为列表'
        ]);
        return $this->fetch();
    }

    /**
     * 创建用户行为
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            //验证
            $actionValidate = new ActionValidate();
            if (!$actionValidate->check($data)) {
                $this->error($actionValidate->getError());
            }
            //判断行为标识是否重复（存在）
            $checkName = ActionModel::where([
                ['name', '=', $data['name']],
                ['status', '>', -1]
            ])->find();

            if ($checkName) {
                $this->error('行为标识重复！');
            }
            //判断行为名称是否重复（存在）
            $checkTitle = ActionModel::where([
                ['title', '=', $data['title']],
                ['status', '>', -1]
            ])->find();

            if ($checkTitle) {
                $this->error('行为名称重复！');
            }
            $data['update_time'] = time();
            $data['status'] = 1;
            $re = ActionModel::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("action_add", "action", $re, UID);
                $this->success('新增成功', 'action/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign(['meta_title' => '新增用户行为']);
            return $this->fetch();
        }
    }

    /**
     * 编辑用户行为
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $info = ActionModel::find($id);
        if (!$info) {
            $this->error('用户行为不存在或已删除！');
        }
        if (Request::isPost()) {
            $data = $_POST;
            //验证
            $actionValidate = new ActionValidate();
            if (!$actionValidate->check($data)) {
                $this->error($actionValidate->getError());
            }
            //判断行为标识是否重复（存在）
            $checkNwhere[] = ['name', '=', $data['name']];
            $checkNwhere[] = ['status', '>', -1];
            $checkNwhere[] = ['id', '<>', $data['id']];
            $checkName = ActionModel::where($checkNwhere)->find();
            if ($checkName) {
                $this->error('行为标识重复！');
            }
            //判断行为名称是否重复（存在）
            /** @var TYPE_NAME $checkTwhere */
            $checkTwhere[] = ['title', '=', $data['title']];
            $checkTwhere[] = ['status', '>', -1];
            $checkTwhere[] = ['id', '<>', $data['id']];
            $checkTitle = ActionModel::where($checkTwhere)->find();
            if ($checkTitle) {
                $this->error('行为名称重复！');
            }
            $data['update_time'] = time();
            $re = ActionModel::update($data);
            if ($re) {
                //添加行为记录
                action_log("action_edit", "action", $data['id'], UID);
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑用户行为'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除用户行为
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = ActionModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("action_del", "action", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 启用禁用用户行为
     */
    public function set_action_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            $action_status = $data['status'] == true ? 'action_status_open' : 'action_status_close';
            $res = ActionModel::update($data);
            if ($res) {
                /** @var TYPE_NAME 添加行为记录 $action_status */
                action_log($action_status, "action", $data['id'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
