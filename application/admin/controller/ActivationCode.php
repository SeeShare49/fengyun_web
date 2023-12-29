<?php

namespace app\admin\controller;

use app\admin\model\ActivationCode as CodeModel;
use think\facade\Request;
use think\facade\View;


class ActivationCode extends Base
{
    /**
     * 激活码列表
     * 玩家根据激活码获取对应的奖励
     */
    public function index()
    {
        $type = trim(input('type'));
        $code = trim(input('code'));
        $where[] = ['status', '>', -1];
        if ($code) {
            $where[] = ['code', 'like', "%$code%"];
        }

        if ($type && $type != 0) {
            $where[] = ['type', '=', $type];
        }

        $lists = CodeModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'type' => $type,
            'code' => $code,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '激活码列表'
        ]);
        return $this->fetch();
    }

    /**
     * 生成激活码
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            if (intval($data['num']) < 1) {
                $this->error('激活码生成数量必须大于0!');
            }

            /** @var TYPE_NAME $codelenth 激活码位数 */
            $codelenth = 8;
            $str = 'ABCDEFGHLJKMNOPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            $len = strlen($str);
            $d = date("Ymd");

            for ($i = 0; $i < $data['num']; $i++) {
                $key = '';
                for ($j = 0; $j < $codelenth; $j++) {
                    $temp = mt_rand(0, $len - 1);
                    $key .= $str[$temp];//激活码
                }
                $foo['cdkey'] = $d . $key;
                $code[] = ['code' => $foo['cdkey'], 'type' => $data['type'], 'status' => 0, 'create_time' => time(), 'update_time' => 0];
            }
            $res = CodeModel::insertAll($code);
            if ($res) {
                action_log("actcode_add", "action", 0, UID);
                $this->success('激活码生成成功!', 'ActivationCode/index');
            } else {
                $this->error('激活码生成失败');
            }
        } else {
            View::assign([
                'meta_title' => '生成激活码'
            ]);
            return View::fetch();
        }
    }


    /**
     * 删除指删除激活码
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $data['update_time'] = time();
        $res = CodeModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("actcode_del", "activation_code", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
}
