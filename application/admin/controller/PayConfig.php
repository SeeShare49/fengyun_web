<?php

namespace app\admin\controller;


use app\admin\model\PayConfig as PayConfigModel;
use app\admin\validate\Notice as NoticeValidate;
use app\admin\validate\PayConfig as PayConfigValidate;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;

class PayConfig extends Base
{
    /**
     * 支付配置列表信息
     */
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['status', '>', -1];
        if ($search) {
            $where[] = ['company_name|app_id|merchant_name', 'like', "%$search%"];
        }

        $lists = PayConfigModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'search' => $search,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '支付配置列表信息'
        ]);
        return $this->fetch();
    }

    /**
     * 新增支付配置信息
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $configValidate = new PayConfigValidate();
            if (!$configValidate->check($data)) {
                $this->error($configValidate->getError());
            }

            //清除上传文件的字段
            unset($data['file']);
            $re = PayConfigModel::insertGetId($data);
            if ($re) {
                //添加行为记录
                try {
                    action_log("pay_config_add", "pay_config", $re, UID);
                } catch (DataNotFoundException $e) {
                } catch (ModelNotFoundException $e) {
                } catch (DbException $e) {
                }
                $this->success('支付配置信息添加成功!', 'pay_config/index');
            } else {
                $this->error("支付配置信息添加失败!");
            }
        } else {
            $this->assign([
                'meta_title' => '新增支付配置信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑支付配置信息
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id = 0)
    {
        $info = PayConfigModel::find($id);
        if (!$info) {
            $this->error("公告信息不存在或已删除!");
        }

        if (Request::isPost()) {
            $data = $_POST;
            $configValidate = new PayConfigValidate();
            if (!$configValidate->check($data)) {
                $this->error($configValidate->getError());
            }

            //清除上传文件的字段
            unset($data['file']);
            $re = PayConfigModel::update($data);
            if ($re) {
                action_log("pay_config_edit", "pay_config", $re, UID);
                $this->success('支付配置信息编辑成功!', 'pay_config/index');
            } else {
                $this->error("支付配置信息编辑失败!");
            }

        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑支付配置信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除选中的支付配置信息
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = PayConfigModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            try {
                action_log("pay_config_del", "pay_config", $ids, UID);
            } catch (DataNotFoundException $e) {
            } catch (ModelNotFoundException $e) {
            } catch (DbException $e) {
            }
            $this->success('支付配置删除成功');
        } else {
            $this->error('支付配置删除失败！');
        }
    }

    /**
     * 启用禁用支付配置信息
     */
    public function set_pay_config_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $pay_config_status = "pay_config_status_show";
            if ($data['status'] == 0) $pay_config_status = "pay_config_status_hide";

            $res = PayConfigModel::update($data);
            if ($res) {
                //添加行为记录
                try {
                    action_log($pay_config_status, "pay_config", $data['id'], UID);
                } catch (DataNotFoundException $e) {
                } catch (ModelNotFoundException $e) {
                } catch (DbException $e) {
                }
                $this->success('支付配置状态修改成功！');
            } else {
                $this->error('支付配置状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
