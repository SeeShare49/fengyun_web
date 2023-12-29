<?php

namespace app\api\controller;

use app\common\test;
use app\pay\model\UserRechargeStatistics;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

class BaiDu
{
    /**
     * 回调地址
     */
    public function notify_url()
    {
        Log::write("百度支付回调来了......");
        $param = Request::param();
        if (isset($param)) {
            $app_id = $param['AppID'];
            $orderNumber = $param['OrderSerial'];
            $cpOrderNumber = $param['CooperatorOrderSerial'];
            $sign = $param['Sign'];
            Log::write("AppID=【" . $app_id . "】,orderSerial=【" . $orderNumber . "】,cooperatorOrderSerial=【" . $cpOrderNumber . "】,sign=【" . $sign . "】");
            $content = $param['Content'];
            Log::write("百度回调返回content:" . $content);
            if (isset($content)) {
                $content_base64_decode = base64_decode($content);
                if (isset($content_base64_decode)) {
                    $content_base64_decode= json_decode($content_base64_decode,true);
                    if ($content_base64_decode['OrderStatus'] == 1) {
                        Db::startTrans();
                        try {
                            $info = \app\pay\model\RechargeData::where('order_id', '=', trim($cpOrderNumber))->find();
                            if ($info && $info['order_status'] == 0) {
                                $new_data['user_id'] = $info['user_id'];
                                $new_data['server_id'] = $info['server_id'];
                                $new_data['recharge_id'] = $info['recharge_id'];
                                $new_data['pay_type'] = $info['pay_type'];
                                $new_data['channel_id'] = $info['channel_id'];
                                $new_data['add_time'] = $info['add_time'];
                                $new_data['amount'] = $info['amount'];
                                $new_data['remark'] = '百度交易号【trade_no】:' . $orderNumber;
                                $new_data['money'] = $info['money'];
                                $new_data['pay_ip'] = $info['pay_ip'];
                                $new_data['order_id'] = $info['order_id'];
                                $new_data['real_server_id'] = $info['real_server_id'];

                                $ret = Db::connect('db_config_main')->table('recharge_data')->insertGetId($new_data);
                                Log::write("写入游戏充值表返回值:" . $ret);
                                if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);

                                //插入或更新用户充值统计数据
                                $user_info = UserRechargeStatistics::where('user_id', '=', $info['user_id'])->find();
                                if ($user_info) {
                                    $user_recharge['user_id'] = $user_info['user_id'];
                                    //判断数据表中的时间戳与当前时间戳的年份、月份是否一致
                                    if (date('y-m', $user_info['update_time']) == date('y-m', time())) {
                                        $user_recharge['month_recharge'] = $user_info['month_recharge'] + $info['money'];
                                    } else {
                                        $user_recharge['month_recharge'] = $info['money'];
                                    }
                                    $user_recharge['total_recharge'] = $user_info['total_recharge'] + $info['money'];
                                    $user_recharge['update_time'] = time();
                                    UserRechargeStatistics::update($user_recharge);
                                } else {
                                    $user_recharge['user_id'] = $info['user_id'];
                                    $user_recharge['month_recharge'] = $info['money'];
                                    $user_recharge['total_recharge'] = $info['money'];
                                    $user_recharge['update_time'] = time();
                                    UserRechargeStatistics::insert($user_recharge);
                                }

                                //修改订单状态
                                $update['id'] = $info['id'];
                                $update['order_status'] = 1;
                                $update['remark'] = '百度交易号【trade_no】:' . $orderNumber;
                                if (!\app\pay\model\RechargeData::update($update)) {
                                    Log::write("订单号【out_trade_no】" . $cpOrderNumber . "状态更新失败！！！");
                                }
                                //充值元宝(命令发送服务器)
                                test::webw_packet_recharge($info['server_id'], $ret);
                            }
                            // 提交事务
                            Db::commit();
                        } catch (Exception $exception) {
                            Log::write('玩家充值事务回滚,exception:' . $exception);
                            Db::rollback();
                        }
                        return json(['ResultCode' => 1]);
                    } else {
                        return json(['code' => false, 'msg' => $content_base64_decode['StatusMsg'], 'uid' => $content_base64_decode['UID'], 'money' => $content_base64_decode['OrderMoney']]);
                    }
                }
            }

            //eyJCYW5rRGF0ZVRpbWUiOiIyMDIxLTA5LTI0IDExOjM1OjE3IiwiRXh0SW5mbyI6IiIsIk1lcmNoYW5kaXNlTmFtZSI6IuWFg+WunSIsIk9yZGVyTW9uZXkiOiIwLjAxIiwiT3JkZXJTdGF0dXMiOjEsIlBheUlkIjoiMTEyNSIsIlN0YXJ0RGF0ZVRpbWUiOiIyMDIxLTA5LTI0IDExOjMzOjI5IiwiU3RhdHVzTXNnIjoi5oiQ5YqfIiwiVUlEIjoiMjkyYTE2NGU4NGExNGYxNTg3YWI4NzAwODY4Y2UwY2YiLCJWb3VjaGVyTW9uZXkiOjB9
        } else {
            Log::write("百度支付成功回调参数错误!");
            echo "failed";
            return json(['code' => false, 'msg' => '百度支付回调参数错误']);
        }

        Log::write("百度支付回调结束......");
    }

    /**
     * 发货通知
     */
    public function deliver_notice()
    {
        $content = "eyJCYW5rRGF0ZVRpbWUiOiIyMDIxLTA5LTIzIDE2OjIwOjEwIiwiRXh0SW5mbyI6IiIsIk1lcmNoYW5kaXNlTmFtZSI6IuWFg+WunSIsIk9yZGVyTW9uZXkiOiIwLjAxIiwiT3JkZXJTdGF0dXMiOjEsIlBheUlkIjoiMTMwMiIsIlN0YXJ0RGF0ZVRpbWUiOiIyMDIxLTA5LTIzIDE2OjIwOjAyIiwiU3RhdHVzTXNnIjoi5oiQ5YqfIiwiVUlEIjoiNzI4MGU5ODQ0NTJjNDQ5MjljMzcwYWU1NzBhZDQzZjciLCJWb3VjaGVyTW9uZXkiOjB9";
        $result = base64_decode($content);
//        {
//            "BankDateTime": "2021-09-23 16:20:10",
//            "ExtInfo": "",
//            "MerchandiseName": "元宝",
//            "OrderMoney": "0.01",
//            "OrderStatus": 1,
//            "PayId": "1302",
//            "StartDateTime": "2021-09-23 16:20:02",
//            "StatusMsg": "成功",
//            "UID": "7280e984452c44929c370ae570ad43f7",
//            "VoucherMoney": 0
//        }

        $ret_str = json_decode($result,true);

       echo $ret_str['OrderStatus'];
       echo PHP_EOL;
        echo $result;
        echo PHP_EOL;
        echo date('Y-m-d H:i:s', time());
    }
}
