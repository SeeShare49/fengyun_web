<?php

namespace app\admin\controller;

use think\facade\Request;
use think\facade\View;

/**
 * 充值金额配置
 */
class RechargeConfig extends Base
{
    public function index()
    {
        //get_appearance




        $config_money = trim(input('money'));
        $where[] = ['status', '>', -1];

        if ($config_money) {
            $where[] = ['money', '=', $config_money];
        }

        $lists = \app\admin\model\RechargeConfig::where($where)
            ->order('id desc,status desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => Request::param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        View::assign([
            'money' => $config_money,
            'page' => $page,
            'lists' => $lists,
            'meta_title' => '充值配置信息'
        ]);
        return View::fetch();
    }

    /**
     * 添加充值配置信息
     */
    public function create()
    {
        if (Request::post()) {
            $data = $_POST;
            $valConfig = new \app\admin\validate\RechargeConfig();
            if (!$valConfig->check($data)) {
                $this->error($valConfig->getError());
            }

            $ret = \app\admin\model\RechargeConfig::insert($data);
            if ($ret) {
                action_log("recharge_config_add", "recharge_config", $ret, UID);
                $this->success('充值配置信息添加成功!', 'recharge_config/index');
            } else {
                $this->error('充值配置信息添加失败!');
            }
        } else {
            View::assign([
                'meta_title' => '新增充值配置'
            ]);
            return View::fetch();
        }
    }


    /**
     * 编辑充值配置信息
     * @param $id
     * @return string
     */
    public function edit($id)
    {
        $info = \app\admin\model\RechargeConfig::find($id);
        if (!$info) {
            $this->error('充值配置信息不存在或已删除!');
        }

        if (Request::post()) {
            $data = $_POST;
            $valConfig = new \app\admin\validate\RechargeConfig();
            if (!$valConfig->check($data)) {
                $this->error($valConfig->getError());
            }

            $ret = \app\admin\model\RechargeConfig::update($data);
            if ($ret) {
                action_log("recharge_config_edit", "recharge_config", $ret, UID);
                $this->success('充值配置信息编辑成功!');
            } else {
                $this->error('充值配置信息编辑失败!');
            }
        } else {
            View::assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑充值配置信息'
            ]);
            return View::fetch();
        }
    }


    /**
     * 删除充值配置信息
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择待删除的充值配置数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $ret = \app\admin\model\RechargeConfig::update($data);
        if ($ret) {
            action_log('recharge_config_del', 'recharge_config', $ids, UID);
            $this->success('充值配置删除成功!');
        } else {
            $this->error('充值配置删除失败!');
        }
        return View::fetch();
    }
}
