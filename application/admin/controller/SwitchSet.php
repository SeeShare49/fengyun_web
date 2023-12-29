<?php

namespace app\admin\controller;

use app\admin\model\Users as UserModel;
use think\facade\Request;
use think\facade\View;

class SwitchSet extends Base
{
    public function index()
    {
        $lists = \app\admin\model\SwitchSet::order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '充值开关设置'
        ]);
        return View::fetch();
    }

    /**
     * 设置平台充值开启或关闭
     **/
    public function set_recharge_switch()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['recharge_switch'] = input('val');
            $recharge_switch = $data['recharge_switch'] == true ? 'recharge_switch_open' : 'recharge_switch_close';
            $ret = \app\admin\model\SwitchSet::update($data);
            if ($ret) {
                action_log($recharge_switch, "switch_set", $data['id'], UID);
                $this->success('操作成功！', 'index');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 设置平台直充开启或关闭
     **/
    public function set_purchase_switch()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['purchase_switch'] = input('val');
            $purchase_switch = $data['purchase_switch'] == true ? 'purchase_switch_open' : 'purchase_switch_close';
            $ret = \app\admin\model\SwitchSet::update($data);
            if ($ret) {
                action_log($purchase_switch, "switch_set", $data['id'], UID);
                $this->success('操作成功！', 'index');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
