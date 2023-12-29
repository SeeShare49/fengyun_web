<?php

namespace app\admin\controller;

use AlibabaCloud\SDK\Cdn\V20180510\Models\DescribeDomainRealTimeBpsDataResponseBody\data;
use app\admin\validate\Users as AdminValidate;
use app\admin\model\Users as UserModel;
use think\facade\Request;

class Users extends Base
{
    /**
     * 管理员列表
     */
    public function index()
    {
        $nickname = trim(input('get.nickname'));
        $map[] = ['status', '>', -1];
        if ($nickname) {
            $map[] = ['username', 'like', "%" . $nickname . "%"];
        }
        $lists = UserModel::where($map)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'nickname' => $nickname,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '用户列表'
        ]);
        return $this->fetch();
    }

    /**
     * 新增管理员
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            //验证
            $userValidate = new AdminValidate();
            if (!$userValidate->check($data)) {
                $this->error($userValidate->getError());
            }
            //验证  确认密码是否与密码相同
            if ($data['repassword'] !== $data['password']) {
                $this->error('两次输入密码不一致！请重新输入！');
            }
            //判断用户名是否重复
//            $checkUsername = UserModel::where('username', $data['username']);
            $checkUsername = UserModel::getUserByUserName($data['username']);
            if (!empty($checkUsername)) {
                $this->error('用户名重复！');
            }

            $datas['username'] = $data['username'];
            $datas['nickname'] = isset($data['nickname']) ? $data['nickname'] : $data['username'];
            $datas['reg_time'] = time();
            $datas['update_time'] = 0;
            $datas['status'] = 1;
            $datas['password'] = yw_ucenter_md5($data['password'], config('UC_AUTH_KEY'));

            $re = UserModel::create($datas);
            if ($re) {
                //添加行为记录
                action_log("member_add", "users", $re, UID);
                $this->success('新增成功', 'users/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign(['meta_title' => '新增用户']);
            return $this->fetch();
        }
    }

    /**
     * 管理员密码修改
     */
    public function editpwd()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $adminValidate = new AdminValidate();
            if (!$adminValidate->scene('editpwd')->check($data)) {
                $this->error($adminValidate->getError());
            }

            if ($data['repassword'] != $data['password']) {
                $this->error('两次输入密码不一致,请重新输入!');
            }
            $member = UserModel::find(UID);
            if (!$member) {
                $this->error('管理员不存在或已删除!');
            }

            if (yw_ucenter_md5($data['oldpassword'], config('UC_AUTH_KEY')) != $member['password']) {
                $this->error('原密码错误,请重新输入!');
            }

            $inputdata['id'] = UID;
            $inputdata['update_time'] = time();
            $inputdata['password'] = yw_ucenter_md5($data['password'], config('UC_AUTH_KEY'));
            $ret = UserModel::update($inputdata);
            if ($ret) {
                action_log('member_edit_pwd', 'users', UID, UID);
                session('uid', null);
                session('ADMIN_MENU_LIST', null);
                $this->success('密码修改成功,请重新登录!', 'login/index');
            } else {
                $this->error('密码修改失败!');
            }
        } else {
            $this->assign(['meta_title' => '管理员密码修改']);
            return $this->fetch();
        }
    }

    /**
     * 重置密码
     * @param $id
     * @return mixed
     */
    public function resetpwd($id)
    {
        if ($id == 1) {
            $this->error('该用户为超级管理员，无法重置其密码！');
        }
        if (Request::isPost()) {
            if (UID != 1) {
                $this->error('您不是超级管理员无法重置其他用户的密码！');
            }

            $member = UserModel::find($id);
            if (!$member) {
                $this->error('用户不存在或已删除！');
            }
            $data = $_POST;
            //验证
            $userValidate = new AdminValidate();
            if (!$userValidate->scene('editpwd')->check($data)) {
                $this->error($userValidate->getError());
            }
            //验证  确认密码是否与密码相同
            if ($data['repassword'] !== $data['password']) {
                $this->error('两次输入密码不一致！请重新输入！');
            }

            $datas['id'] = $id;
            $datas['update_time'] = time();
            $datas['password'] = yw_ucenter_md5($data['password'], config('UC_AUTH_KEY'));
            $re = UserModel::update($datas);
            if ($re) {
                action_log("member_reset_pwd", "users", $id, UID);
                $this->success('密码重置成功', '');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->assign(['meta_title' => '重置密码']);
            return $this->fetch();
        }
    }

    /**
     * 修改管理员昵称
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if (Request::isPost()) {
            $nickname = input('nickname');
            if (!$nickname) {
                $this->error('请输入管理员昵称!');
            }

            $data['id'] = $id;
            $data['nickname'] = $nickname;
            $data['update_time'] = time();
            $ret = UserModel::update($data);
            if ($ret) {
                action_log('member_edit_nickname', 'users', $id, UID);
                $this->success('管理员昵称修改成功!');
            } else {
                $this->error('管理员昵称修改失败!');
            }
        } else {
            $member = UserModel::find($id);
            $this->assign([
                'id' => $id,
                'member' => $member,
                'meta_title' => '编辑管理员'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除管理员
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请至少选择一项操作的数据!');
        }

        if (in_array(1, $ids)) {
            $this->error('超级管理员无法被删除!');
        }

        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $ret = UserModel::where($where)->update($data);
        if ($ret) {
            action_log('member_del', 'users', $ids, UID);
            $this->success('管理员用户删除成功!', 'index');
        } else {
            $this->error('管理员用户删除失败!');
        }
    }

    /**
     * 管理员用户授权，分配权限组
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
        if (Request::isPost()) {
            $data['id'] = input('id/d');
            if ($data['id'] == 1) {
                $this->error('该用户为超级管理员,无法授权!');
            }

            $data['group_id'] = input('group_id/d');
            $ret = db('users')->update($data);
            if ($ret) {
                session('ADMIN_MEMBER_RULES', null);
                action_log('adminmember_auth', 'users', $data['id'], UID);
                $this->success('管理员授权、权限分配成功!');
            } else {
                $this->error('管理员授权、权限分配失败!');
            }
        } else {
            $map[] = ['status', '>', -1];
            $lists = db('auth_group')->where($map)->select();
            $member = db('users')->find($id);

            $this->assign([
                'id' => $id,
                'member' => $member,
                'lists' => $lists
            ]);
            return $this->fetch();
        }
    }

    /**
     * 启用禁用管理员用户状态
     */
    /**
     * 启用禁用用户权限
     */
    public function set_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            if ($data['id'] == 1) {
                $this->error('无法启用或禁用超级管理员状态!');
            }
            $data['status'] = input('val');
            $member_status = $data['status'] == true ? 'users_status_open' : 'users_status_close';
            $ret = UserModel::update($data);
            if ($ret) {
                action_log($member_status, "users", $data['id'], UID);
                $this->success('操作成功！', 'index');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
