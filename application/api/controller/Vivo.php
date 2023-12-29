<?php

namespace app\api\controller;


use app\admin\model\RechargeData;
use app\common\test;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

/**
 * Vivo渠道支付类
 **/
class Vivo
{
    public function index()
    {
        //local sRechargeUrl = ORDER_URL .. "server_id=" .. nServerId .. "&user_id=" .. nActorId .. "&recharge_id=" ..
        // nRechargeId .. "&type=" .. nRechargeType.."&old_server_id="..nOldServerId.."&nChanneId="..nChanneId

        Log::write("Vivo请求来了....");
        $param = Request::param();

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
            $data['pay_type'] = 4;//对应支付配置表ID
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
     * vivo同步回调地址
     **/
    public function notify_url()
    {
        /**
         * 'orderNumber' => '2021060821100030900018010130',
         * 'payTime' => '20210608211003',
         * 'signature' => 'f611ce5d1450651c7267e140071f4396',
         * 'cpId' => '9e1a13934fae2247890f',
         * 'cpOrderNumber' => 'Qd7_2147484651820210608210956',
         * 'uid' => 'ec50fd16c0da964b',
         * 'orderAmount' => '1',
         * 'tradeStatus' => '0000',
         * 'appId' => '105474162',
         * 'respMsg' => '交易成功',
         * 'respCode' => '200',
         * 'tradeType' => '01',
         * 'signMethod' => 'MD5',
         **/


        Log::write("Vivo 回调该接口了！！！！");
        echo 'Vivo 回调该接口了！！！！';


        $result = Request::param();
        if (isset($result)) {
            Log::write("tradeStatus:" . $result['tradeStatus']);
            if ($result['tradeStatus'] == '0000') {
                $cpOrderNumber = $result['cpOrderNumber'];
                $orderNumber = $result['orderNumber'];

                Log::write("vivo 同步回调返回成功");
                Log::write("cpOrderNumber:" . $cpOrderNumber);
                Log::write("orderNumber:" . $orderNumber);
                Db::startTrans();
                try {
                    $info = \app\pay\model\RechargeData::where('order_id', '=', trim($cpOrderNumber))->find();
                    if ($info && $info['order_status'] == 0) {
                        //$new_data['id'] = $info['id'];
                        $new_data['user_id'] = $info['user_id'];
                        $new_data['server_id'] = $info['server_id'];
                        $new_data['recharge_id'] = $info['recharge_id'];
                        $new_data['pay_type'] = $info['pay_type'];
                        $new_data['channel_id'] = $info['channel_id'];
                        $new_data['add_time'] = $info['add_time'];
                        $new_data['amount'] = $info['amount'];
                        $new_data['remark'] = 'Vivo交易号【trade_no】:' . $orderNumber;
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
                        //$update['status'] = 1;
                        $update['order_status'] = 1;
                        $update['remark'] = 'Vivo交易号【trade_no】:' . $orderNumber;
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
            }
        } else {
            echo 'fail';
        }

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
