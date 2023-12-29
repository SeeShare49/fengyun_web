<?php

namespace app\admin\controller;


use AlibabaCloud\SDK\Cdn\V20180510\Models\DescribeDomainRealTimeBpsDataResponseBody\data;
use app\admin\model\Action as ActionModel;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;
use app\admin\validate\ChannelShare as ShareValidate;

class ChannelShare extends Base
{
    /**
     * 渠道分成比例列表
     **/
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['1', '=', 1];
        if ($search) {
            $where[] = ['channel_name', 'like', "%$search%"];
        }
        $lists = \app\admin\model\ChannelShare::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'search' => $search,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '应用渠道分成比例列表'
        ]);
        return View::fetch();
    }

    /**
     * 添加渠道分成比例信息
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $shareValidate = new ShareValidate();
            if (!$shareValidate->check($data)) {
                $this->error($shareValidate->getError());
            }

            $channel_id = $data['channel_id'];
            Log::write("channel share create sql:".\app\admin\model\ChannelShare::where('channel_id', '=', $channel_id)->fetchSql(true)->find());
            $checkInfo = \app\admin\model\ChannelShare::where('channel_id', '=', $channel_id)->find();
            if ($checkInfo) {
                $this->error('渠道ID:【' . $channel_id . '】已存在,请勿重复添加!!!');
            }

            $data['channel_name'] = get_channel_name($channel_id);
            $data['official_share_ratio'] = 100 - intval($data['share_ratio']);
            $ret = \app\admin\model\ChannelShare::insertGetId($data);
            if ($ret) {
                //添加行为记录
                action_log("channel_share_add", "channel_share", $ret, UID);
                $this->success('渠道分成比例新增成功', 'channel_share/index');
            } else {
                $this->error('渠道分成比例新增失败');
            }
        } else {
            $channel_list = \app\admin\model\Channel::select();
            View::assign([
                'channel_list' => $channel_list,
                'meta_title' => '添加渠道分成比例'
            ]);
            return View::fetch();
        }
    }

    /**
     * 编辑渠道分成比例信息
     * @param $id
     */
    public function edit($id)
    {
        $info = \app\admin\model\ChannelShare::find($id);

        $channel_list = \app\admin\model\Channel::select();
        if (!$info) {
            $this->error('渠道分成比例信息不存在或已删除!');
        }

        if (Request::isPost()) {
            $data = $_POST;

            $shareValidate = new ShareValidate();
            if (!$shareValidate->check($data)) {
                $this->error($shareValidate->getError());
            }

            $where[] = ['channel_id', '=', trim($data['channel_id'])];
            $where[] = ['id', '<>', $id];
            $checkInfo = \app\admin\model\ChannelShare::where($where)->find();
            if ($checkInfo) {
                $this->error('渠道ID:【' . $id . '】分成比例信息重复,请核实信息!');
            }

            $data['channel_name'] = get_channel_name($data['channel_id']);
            $data['official_share_ratio'] = 100 - intval($data['share_ratio']);
            $ret = \app\admin\model\ChannelShare::update($data);
            if ($ret) {
                //添加行为记录
                action_log("channel_share_edit", "channel_share", $ret, UID);
                $this->success('渠道分成比例编辑成功', 'channel_share/index');
            } else {
                $this->error('渠道分成比例编辑失败!');
            }
        } else {
            View::assign([
                'id' => $id,
                'info' => $info,
                'channel_list' => $channel_list,
                'meta_title' => '编辑渠道分成比例信息'
            ]);
            return View::fetch();
        }
    }

    /**
     * 删除应用渠道分成比例信息
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $res = \app\admin\model\ChannelShare::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("channel_share_del", "channel_share", $ids, UID);
            $this->success('应用渠道分成比例信息删除成功');
        } else {
            $this->error('应用渠道分成比例信息删除失败！');
        }
    }

    /**
     * 启用禁用渠道分成比例状态
     */
    public function set_share_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $action_status = "share_status_open";
            if ($data['status'] == 0) $action_status = "share_status_close";

            $res = \app\admin\model\ChannelShare::update($data);
            if ($res) {
                /** @var TYPE_NAME 添加行为记录 $action_status */
                action_log($action_status, "channel_share", $data['id'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
