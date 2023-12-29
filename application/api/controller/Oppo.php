<?php

namespace app\api\controller;

use app\common\test;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

/**
 * OPPO SDK支付
 **/
class Oppo
{
    public function index()
    {
        Log::write("Oppo请求来了....");
        $param =Request::param();

        if (isset($param)) {
            $user_id = $param['user_id'];
            $recharge_id = $param['recharge_id'];
            $type = $param['type'];
            $channel_id = $param['nChanneId'];
            $money = 0;//充值金额
            $amount = 0;//对应的充值道具数量
            if (isset($user_id) && isset($recharge_id) && isset($type)) {
                $info = $this->get_recharge_by_csv($recharge_id, $type);
                if (empty($info)) {
                    return json(['code' => false, 'msg' => '未匹配对应的充值信息']);
                }
                $money = isset($info['money']) ? $info['money'] : 0; //Vivo渠道金额单位:分
                $amount = $info['amount'];
            }

            $data['user_id'] = $user_id;
            $data['server_id'] = $param['server_id'];
            $data['recharge_id'] = $recharge_id;
            $data['amount'] = $amount;
            $data['money'] = $money;
            $data['pay_type'] = 5;//对应支付配置表ID
            $data['order_id'] = 'Qd' . $channel_id . '_' . $user_id . date('YmdHis');//渠道ID_+用户ID+日期、
            $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
            $data['channel_id'] = $channel_id;
            $data['pay_ip'] = request()->ip();
            $data['real_server_id'] = $param['old_server_id'];
            if (\app\pay\model\RechargeData::insertGetId($data)) {
                return json(['code' => true, 'order_id' => $data['order_id'], 'money' => $money, 'amount' => $amount, 'msg' => '订单创建成功']);
            } else {
                return json(['code' => false, 'order_id' => $data['order_id'], 'money' => $money, 'amount' => $amount, 'msg' => '订单创建失败']);
            }

        } else {
            return json(['code' => false, 'msg' => '参数错误']);
        }
    }

    /**
     * oppo同步回调地址
     **/
    public function notify_url()
    {
        Log::write("OPPO 回调该接口了！！！！");
        echo 'OPPO 回调该接口了！！！！';
        $result = Request::param();
        echo $result;
        var_dump($result);
        echo 'success';


    }

    /**
     * 通过recharge_id与type获取充值信息
     * @param $recharge_id  充值ID
     * @param $type         充值类型
     * @return array
     */
    public function get_recharge_by_csv($recharge_id, $type)
    {
        $file_name = '../public/csv/recharge.csv';
        $file_open = fopen($file_name, 'r');
        // $data = fgetcsv($file_open, 100, ',');
        $count = 1;
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count >= 1) {
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[0] == $recharge_id && $data[1] == $type) {
                        $item['recharge_id'] = $data[0];
                        $item['type'] = $data[1];
                        $item['icon'] = $data[2];
                        $item['money'] = $data[3];
                        $item['amount'] = $data[4];
                    }
                    break;
                }
            }
            $count++;
        }
        fclose($file_open);
        return $item;
    }
}
