<?php


namespace app\api\controller;

use think\facade\Log;
use think\facade\Request;

/**
 * 华为渠道支付类
 **/
class HuaWei
{
    public function index()
    {
        Log::write("发起华为渠道支付请求!!!");
        $params = Request::param();
        if (isset($params)) {
            $user_id = $params['user_id'];
            $recharge_id = $params['recharge_id'];
            $type = $params['type'];
            $channel_id = $params['nChanneId'];
            $money = 0;//充值金额
            $amount = 0;//对应的充值道具数量
            if (isset($user_id) && isset($recharge_id) && isset($type)) {
                $info = $this->get_recharge_by_csv($recharge_id, $type);
                if (empty($info)) {
                    return json(['code' => false, 'msg' => '未匹配对应的充值信息']);
                }
                $money = isset($info['money']) ? $info['money'] : 0;
                $amount = $info['amount'];
            }

            $data['user_id'] = $user_id;
            $data['server_id'] = $params['server_id'];
            $data['recharge_id'] = $recharge_id;
            $data['amount'] = $amount;
            $data['money'] = $money;
            $data['pay_type'] = 4;//对应支付配置表ID
            $data['order_id'] = 'Qd' . $channel_id . '_' . $user_id . date('YmdHis');//渠道ID_+用户ID+日期、
            $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
            $data['channel_id'] = $channel_id;
            $data['pay_ip'] = request()->ip();
            $data['real_server_id'] = $params['old_server_id'];
            if (\app\pay\model\RechargeData::insertGetId($data)) {
                return json(['code' => true, 'order_id' => $data['order_id'], 'money' => $money, 'amount' => $amount, 'msg' => '华为渠道支付订单创建成功']);
            } else {
                return json(['code' => false, 'order_id' => $data['order_id'], 'money' => $money, 'amount' => $amount, 'msg' => '华为渠道支付订单创建失败']);
            }
        } else {
            return json(['code' => false, 'msg' => '华为请求支付参数错误']);
        }
    }

    /**
     * 华为渠道支付同步回调地址
     **/
    public function notify_url()
    {
        Log::write("调用华为渠道支付回调函数!!!");
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