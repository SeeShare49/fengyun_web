<?php

namespace app\admin\controller;

use app\admin\model\Welfare as WelfareModel;
use app\common\ServerManage;
use think\facade\View;
use think\Request;

define('GROUP_ID', config('admin.GROUP_ID'));

class UserServer extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    { 
        $user_name = trim(input('user_name'));
        $where[] = ['1', '=', 1];
        if ($user_name) {
            $where[] = ['user_name', 'like', "%$user_name%"];
        }
        $lists = \app\admin\model\UserServer::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'user_name' => $user_name,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '管理员关联服务器列表',
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
        if (GROUPID == GROUP_ID) {
            $s_ids = get_user_server_list(UID);
            $str_ids = explode(',', $s_ids);
            var_dump("获取到的服务器列表:".$str_ids);
            var_dump(count($str_ids));
            $server_list = ServerManage::getServerListByIds($s_ids);
        } else {
            $server_list = ServerManage::getServerList();
        }
        $user_list = \app\admin\model\Users::where('group_id', '=', GROUP_ID)->select();

        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $check[] = ['server_id', '=', $data['server_id']];
            $check[] = ['uid', '=', $data['uid']];
            $checkInfo = \app\admin\model\UserServer::where($check)->find();
            if ($checkInfo) {
                $this->error('关联信息已存在,请勿重复添加！');
            }

            $uid = $data['uid'];
            $newData['uid'] = $uid;
            $newData['user_name'] = \app\admin\model\Users::getUserNameById($uid);
            $newData['server_id'] = $data['server_id'];
            $ret = \app\admin\model\UserServer::insertGetId($newData);
            if ($ret) {
                action_log("user_server_edit", "channel", $ret, UID);
                $this->success('管理员关联服务器消息添加成功！', '');
            } else {
                $this->error('管理员关联服务器消息添加失败！', '');
            }
        } else {
            View::assign([
                'server_list' => $server_list,
                'user_list' => $user_list,
                'meta_title' => '添加管理员关联服务器消息'
            ]);
            return View::fetch();
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
        $user_list = \app\admin\model\Users::where('group_id', '=', GROUP_ID)->select();
        $server_list = ServerManage::getServerList();
        $info = \app\admin\model\UserServer::find($id);
        if (!$info) {
            $this->error('关联信息不存在或已删除！');
        }
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $check[] = ['server_id', '=', $data['server_id']];
            $check[] = ['uid', '=', $data['uid']];
            $check[] = ['id', '<>', $data['id']];
            $checkInfo = \app\admin\model\UserServer::where($check)->find();
            if ($checkInfo) {
                $this->error('关联信息未做任何修改！');
            }

            $uid = $data['uid'];
            $newData['uid'] = $uid;
            $newData['user_name'] = \app\admin\model\Users::getUserNameById($uid);
            $newData['server_id'] = $data['server_id'];
            $ret = \app\admin\model\UserServer::where('id','=',$id)->update($newData);
            if ($ret) {
                action_log("user_server_edit", "channel", $data['id'], UID);
                $this->success('管理员关联服务器消息编辑成功！', '');
            } else {
                $this->error('管理员关联服务器消息编辑失败！', '');
            }
        } else {
            View::assign([
                'server_list' => $server_list,
                'user_list' => $user_list,
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑管理员关联服务器消息',
            ]);
            return View::fetch();
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
        $res = \app\admin\model\UserServer::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("user_server_del", "user_server", $ids, UID);
            $this->success('管理员关联服务器删除成功');
        } else {
            $this->error('管理员关联服务器删除失败！');
        }
    }
}
