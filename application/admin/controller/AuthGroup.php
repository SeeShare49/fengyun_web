<?php

namespace app\admin\controller;

use app\admin\validate\Authgroup as AuthManagerValidate;

class AuthGroup extends Base
{
    //权限配置首页
    public function index()
    {
        $lists = db('auth_group')
            ->where('status', '>', -1)
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'page' => $page,
            'lists' => $lists,
            'meta_title' => '权限配置首页'
        ]);
        return $this->fetch();
    }


    /**
     * 创建（编辑）用户权限
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create($id = 0)
    {
        if (request()->isPost()) {
            $data = $_POST;
            //验证
            $authManagerValidate = new AuthManagerValidate();
            if (!$authManagerValidate->check($data)) {
                $this->error($authManagerValidate->getError());
            }
            //判断用户组名称是否重复
            $checkwhere[] = ['title', '=', $data['title']];
            $checkwhere[] = ['status', '>', -1];
            $checkTitle = db('auth_group')->where($checkwhere)->find();
            if ($checkTitle) {
                $this->error('用户组名称重复！');
            }
            $data['type'] = 1;
            $data['status'] = 1;
            $data['module'] = 'admin';
            $data['allow_ip'] = $data['allow_ip'];
            $re = db('auth_group')->insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("adminauthgroup_add", "auth_group", $re, UID);
                $this->success('新增成功', '');
                //未刷新界面
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign(['meta_title' => '新增用户组']);
            return $this->fetch();
        }
    }


    /**
     * 编辑用户组
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
        $info = db('auth_group')->find($id);
        if (!$info) {
            $this->error('用户权限组不存在或已删除!');
        }

        if (\request()->isPost()) {
            $data = $_POST;
            $authManagerValidate = new AuthManagerValidate();
            if (!$authManagerValidate->check($data)) {
                $this->error($authManagerValidate->getError());
            }

            //判断用户组是否已存在
            $checkWhere[] = ['title', '=', $data['title']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];

            $isExistTitle = db('auth_group')->where($checkWhere)->find();
            if ($isExistTitle) {
                $this->error('用户组名称重复,请重新输入!');
            }

            $ret = db('auth_group')->update($data);
            if ($ret) {
                action_log('adminauthgroup_edit', 'auth_group', $id, UID);
                $this->success('用户权限组修改成功!');
            } else {
                $this->error('用户权限组修改失败!');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑用户权限组'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除用户权限组
     * @param $id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del($id)
    {
        $id = input('ids/a');
        if (empty($id)) {
            $this->error('请选择至少一项需操作的数据!');
        }

        $where[] = ['id', 'in', $id];
        $data['status'] = -1;
        $ret = db('auth_group')->where($where)->update($data);
        if ($ret) {
            db('users')->where('group_id', 'in', $id)->update(['group_id' => 0]);
            //添加行为记录
            action_log('admin_auth_group_del', 'auth_group', $id, UID);
            $this->success('用户组权限删除成功!');
        } else {
            $this->error('用户组权限删除失败!');
        }
    }

    /**
     * 访问授权页面
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function access($id)
    {
        if (request()->isPost()) {
            $rule = input('rule/a');
            if (!$rule) {
                $this->error('请选择要授权的访问！');
            }
            $data['rules'] = implode(',', $rule);
            $res = db('auth_group')->where('id', $id)->update($data);
            if ($res) {
                session('ADMIN_MEMBER_RULES', null);
                //添加行为记录
                action_log("admin_auth_group_access", "auth_group", $id, UID);
                $this->success('操作成功', 'index');
            } else {
                $this->error('操作失败！');
            }
        } else {
            //拉取所有后台所有菜单
            $lists = db('menu')->where('status', 1)->order('sort asc')->select();
            $lists = list_to_tree($lists, 0);

            $authGroup = db('auth_group')->find($id);
            $this->assign([
                'id' => $id,
                'authGroup' => $authGroup,
                'lists' => $lists,
                'meta_title' => '访问授权'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 访问授权页面
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function auth($id)
    {
        if (\request()->isPost()) {
            $username = input('username');
            if (!$username) {
                $this->error('请输入需授权的用户名!');
            }

            $ret = db('users')->where('id', $id)->find();
            if (!$ret) {
                $this->error('用户不存在或已删除!');
            }

            if ($ret['id'] == 1) {
                $this->error('该用户为超级管理员,无法授权!');
            }

            if ($ret['group_id'] != 0) {
                $this->error('该用户已分配至其他组,无法授权!');
            }

            $data['id'] = $ret['id'];
            $data['group_id'] = $id;
            $ret = db('users')->update($data);
            if ($ret) {
                session('ADMIN_MEMBER_RULES', null);
                //                添加行为记录
                action_log("admin_auth_group_user", "users", $data['id'], UID);
                $this->success('用户授权操作成功!');
            } else {
                $this->error('用户授权操作失败！');
            }
        } else {
            $authGroup = db('auth_group')->find($id);
            $this->assign('authGroup', $authGroup);

            $where[] = ['group_id', '=', $id];
            $where[] = ['status', '>', -1];
            $lists = db('users')->where($where)->select();
            $this->assign([
                'id' => $id,
                'lists' => $lists,
                'meta_title' => '成员授权'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 成员取消授权页面
     * @param $id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function user_cancel_auth($id)
    {
        $data['id'] = $id;
        $data['group_id'] = 0;
        $res = db('users')->where('id', $id)->update($data);
        if ($res) {
            //添加行为记录
            action_log("admin_auth_group_user_cancel", "users", $id, UID);
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }


    /**
     * 启用禁用用户权限
     */
    public function set_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            $auth_group_status = $data['status'] == true ? 'admin_auth_group_status_open' : 'admin_auth_group_status_close';
            $res = db('auth_group')->update($data);
            if ($res) {
                action_log($auth_group_status, "auth_group", $data['id'], UID);
                $this->success('操作成功！', 'index');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
