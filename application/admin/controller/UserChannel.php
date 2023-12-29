<?php

namespace app\admin\controller;

use app\admin\model\Welfare as WelfareModel;
use app\common\ServerManage;
use think\facade\View;
use think\Request;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));

class UserChannel extends Base
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
        $lists = \app\admin\model\UserChannel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'user_name' => $user_name,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '管理员关联渠道列表',
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
        if (\think\facade\Request::isPost()) {
            $data = $_POST;

            $uid = $data['uid'];
            $newData['uid'] = $uid;
            $newData['user_name'] = \app\admin\model\Users::getUserNameById($uid);
            $newData['channel_ids'] = $data['channel_ids'];
            $ret = \app\admin\model\UserChannel::insertGetId($newData);
            if ($ret) {
                action_log("user_channel_edit", "user_channel", $ret, UID);
                $this->success('管理员关联渠道消息添加成功！', '');
            } else {
                $this->error('管理员关联渠道消息添加失败！', '');
            }
        } else {
            $user_list = \app\admin\model\Users::where('group_id', '=', MIX_GROUP_ID)->select();
            $channel_list = \app\admin\model\Channel::select();
            View::assign([
                'channel_list' => $channel_list,
                'user_list' => $user_list,
                'meta_title' => '添加管理员关联渠道消息'
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
        $user_list = \app\admin\model\Users::where('group_id', '=', MIX_GROUP_ID)->select();
        $channel_list = \app\admin\model\Channel::select();
        $info = \app\admin\model\UserChannel::find($id);
        if (!$info) {
            $this->error('关联渠道信息不存在或已删除！');
        }
        if (\think\facade\Request::isPost()) {
            $data = $_POST;

            $uid = $data['uid'];
            $newData['uid'] = $uid;
            $newData['user_name'] = \app\admin\model\Users::getUserNameById($uid);
            $newData['channel_ids'] = $data['channel_ids'];
            $ret = \app\admin\model\UserChannel::where('id', '=', $id)->update($newData);
            if ($ret) {
                action_log("user_channel_edit", "channel", $data['id'], UID);
                $this->success('管理员关联服务器消息编辑成功！', '');
            } else {
                $this->error('管理员关联服务器消息编辑失败！', '');
            }
        } else {
            View::assign([
                'channel_list' => $channel_list,
                'user_list' => $user_list,
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑管理员关联渠道消息',
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
        $res = \app\admin\model\UserChannel::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("user_server_del", "user_server", $ids, UID);
            $this->success('管理员关联渠道删除成功');
        } else {
            $this->error('管理员关联渠道删除失败！');
        }
    }
}
