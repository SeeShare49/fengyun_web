<?php

namespace app\admin\controller;

use think\facade\View;

class Channel extends Base
{
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['1', '=', 1];

        if ($search) {
            $where[] = ['channel_name|channel_mark', 'like', "%$search%"];
        }

        $lists = \app\admin\model\Channel::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'search' => $search,
            'lists' => $lists,
            'page' => $page,
            'empty'=>'<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '应用渠道列表'
        ]);
        return View::fetch();
    }

    /**
     * 新增应用渠道
     */
    public function create()
    {
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            //验证
            $channelValidate = new \app\admin\validate\Channel();
            if (!$channelValidate->check($data)) {
                $this->error($channelValidate->getError());
            }
            //判断行为标识是否重复（存在）
            $checkName = \app\admin\model\Channel::where([
                ['channel_name', '=', $data['channel_name']]
            ])->find();

            if ($checkName) {
                $this->error('渠道名称重复！');
            }
            //判断行为名称是否重复（存在）
            $checkMark = \app\admin\model\Channel::where([
                ['channel_mark', '=', $data['channel_mark']],
            ])->find();

            if ($checkMark) {
                $this->error('渠道标识重复！');
            }
            $re = \app\admin\model\Channel::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("channel_add", "channel", $re, UID);
                $this->success('应用渠道新增成功', 'channel/index');
            } else {
                $this->error('应用渠道新增失败');
            }
        } else {
            $this->assign(['meta_title' => '新增应用渠道']);
            return $this->fetch();
        }
    }


    /**
     * 编辑应用渠道信息
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $info = \app\admin\model\Channel::find($id);
        if (!$info) {
            $this->error('应用渠道不存在或已删除！');
        }
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            //验证
            $channelValidate = new \app\admin\validate\Channel();
            if (!$channelValidate->check($data)) {
                $this->error($channelValidate->getError());
            }
            //判断行为标识是否重复（存在）
            $checkChannel[] = ['channel_name', '=', $data['channel_name']];
            $checkChannel[] = ['id', '<>', $data['id']];
            $checkName = \app\admin\model\Channel::where($checkChannel)->find();
            if ($checkName) {
                $this->error('渠道名称重复！');
            }
            //判断行为名称是否重复（存在）
            $checkMark[] = ['channel_mark', '=', $data['channel_mark']];
            $checkMark[] = ['id', '<>', $data['id']];
            $checkTitle = \app\admin\model\Channel::where($checkMark)->find();
            if ($checkTitle) {
                $this->error('渠道标识重复！');
            }
            $re = \app\admin\model\Channel::update($data);
            if ($re) {
                //添加行为记录
                action_log("channel_edit", "channel", $data['id'], UID);
                $this->success('应用渠道编辑成功', '');
            } else {
                $this->error('应用渠道编辑失败');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑应用渠道信息'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除应用渠道
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $res = \app\admin\model\Channel::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("channel_del", "channel", $ids, UID);
            $this->success('应用渠道删除成功');
        } else {
            $this->error('应用渠道删除失败！');
        }
    }
}
