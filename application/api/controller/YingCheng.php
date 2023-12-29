<?php

namespace app\api\controller;

use app\admin\model\PurchaseData;
use app\admin\model\RechargeData;
use app\common\test;
use app\pay\model\UserRechargeStatistics;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\Request;

class YingCheng
{
    public function index()
    {
        $param = \think\facade\Request::param();
        if (isset($param)) {
            $user_id = $param['user_id'];
            $recharge_id = $param['recharge_id'];
            $type = $param['type'];
            $channel_id = $param['nChanneId'];
            $server_id = $param['server_id'];
            $old_server_id = $param['old_server_id'];
            $money = 0;//充值金额
            $amount = 0;//对应的充值道具数量
            $order_id = '';
            if (isset($user_id) && isset($recharge_id) && isset($type)) {
                if ($type == 1) {
                    //商城充值
                    $info = $this->get_recharge_by_csv($recharge_id);
                    if (empty($info)) {
                        return json(['code' => false, 'msg' => '未匹配对应的充值信息']);
                    }
                    $money = isset($info['money']) ? $info['money'] : 0;
                    $amount = $info['amount'];
                    $order_id = 'YC_' . $user_id . date('YmdHis');
                } elseif ($type == 2) {
                    //直购充值
                    $info = $this->get_buy_direct_recharge_by_csv($recharge_id);
                    if (empty($info)) {
                        return json(['code' => false, 'msg' => '未匹配对应的直购充值数据']);
                    }
                    $money = isset($info['money']) ? $info['money'] : 0;
                    $amount = 1;
                    $data['purchase_name'] = $info['prop_name'];
                    $order_id = 'ZG_' . $user_id . date('YmdHis');
                } else {
                    return json(['code' => false, 'msg' => '类型参数错误']);
                }
            } else {
                return json(['code' => false, 'msg' => '创建订单请求参数错误']);
            }

            $data['user_id'] = $user_id;
            $data['server_id'] = $server_id;
            $data['recharge_id'] = $recharge_id;
            $data['amount'] = $amount;
            $data['money'] = $money;
            $data['pay_type'] = 1;//对应支付配置表ID
            $data['order_id'] = $order_id;
            $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
            $data['channel_id'] = $channel_id;
            $data['pay_ip'] = request()->ip();
            $data['real_server_id'] = $old_server_id;
            if ($type == 1) {
                if (\app\pay\model\RechargeData::insertGetId($data)) {
                    return json(['code' => true, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '商城订单创建成功']);
                } else {
                    return json(['code' => false, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '商城订单创建失败']);
                }
            } elseif ($type == 2) {
                if (PurchaseData::insertGetId($data)) {
                    return json(['code' => true, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '直购订单创建成功']);
                } else {
                    return json(['code' => false, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '直购订单创建失败']);
                }
            } else {
                return json(['code' => false, 'msg' => '类型参数错误']);
            }
        } else {
            return json(['code' => false, 'msg' => '参数错误']);
        }
    }

    /**
     * 通过recharge_id与type获取充值信息
     * @param $recharge_id  充值ID
     * @return array
     */
    public function get_recharge_by_csv($recharge_id)
    {
        $file_name = '../public/csv/recharge.csv';
        $file_open = fopen($file_name, 'r');
        $count = 1;
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count >= 1) {
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[0] == $recharge_id) {
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

    /**
     * 根据recharge_id获取直购表配置参数信息
     **/
    public function get_buy_direct_recharge_by_csv($recharge_id)
    {
        $file_name = '../public/csv/BuyDirect.csv';
        $file_open = fopen($file_name, 'r');
        $count = 1;
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count >= 1) {
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[0] == $recharge_id) {
                        $item['recharge_id'] = $data[0];
                        $item['money'] = $data[1];
                        $item['prop_name'] = $data[3];
                    }
                    break;
                }
            }
            $count++;
        }
        fclose($file_open);
        return $item;
    }

    /**
     * 盈诚支付回调
     */
    public function notify_url()
    {
        $result = \think\facade\Request::param();
        if (isset($result)) {
            $call_back_key = '28575332784884070868256445692465';
            $response = $this->decode($result['nt_data'], $call_back_key);
            $ret_str = $this->xml_to_data($response);
            var_dump("ret str:" . $ret_str);
            if ($ret_str['message']['status'] == "0") {
                $cpOrderNumber = $ret_str['message']['game_order'];
                $orderNumber = $ret_str['message']['order_no'];
                Log::write("cpOrderNumber:" . $cpOrderNumber);
                Log::write("orderNumber:" . $orderNumber);
                Db::startTrans();
                try {
                    Log::write("订单号前缀是否匹配YC_:".stristr(trim($cpOrderNumber), "YC_"));
                    if (stristr(trim($cpOrderNumber), "YC_")) {
                        $info = \app\pay\model\RechargeData::where('order_id', '=', trim($cpOrderNumber))->find();
                        if ($info && $info['order_status'] == 0) {
                            $new_data['user_id'] = $info['user_id'];
                            $new_data['server_id'] = $info['server_id'];
                            $new_data['recharge_id'] = $info['recharge_id'];
                            $new_data['pay_type'] = $info['pay_type'];
                            $new_data['channel_id'] = $info['channel_id'];
                            $new_data['add_time'] = $info['add_time'];
                            $new_data['amount'] = $info['amount'];
                            $new_data['remark'] = '盈诚游戏交易号【trade_no】:' . $orderNumber;
                            $new_data['money'] = $info['money'];
                            $new_data['pay_ip'] = $info['pay_ip'];
                            $new_data['order_id'] = $info['order_id'];
                            $new_data['real_server_id'] = $info['real_server_id'];

                            $ret = Db::connect('db_config_main')->table('recharge_data')->insertGetId($new_data);
                            if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);

                            /* 新增或更新用户充值统计数据 */
                            $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                            //修改订单状态
                            $update['id'] = $info['id'];
                            $update['order_status'] = 1;
                            $update['remark'] = '盈诚游戏交易号【trade_no】:' . $orderNumber;
                            if (!\app\pay\model\RechargeData::update($update)) {
                                Log::write("订单号【out_trade_no】" . $cpOrderNumber . "状态更新失败！！！");
                            }
                            //充值元宝(命令发送服务器)
                            test::webw_packet_recharge($info['server_id'], $ret);
                        }
                    } else {
                        $info = PurchaseData::where('order_id', '=', trim($cpOrderNumber))->find();
                        if ($info && $info['order_status'] == 0) {
                            $new_data['user_id'] = $info['user_id'];
                            $new_data['server_id'] = $info['server_id'];
                            $new_data['recharge_id'] = $info['recharge_id'];
                            $new_data['pay_type'] = $info['pay_type'];
                            $new_data['channel_id'] = $info['channel_id'];
                            $new_data['purchase_name'] = $info['purchase_name'];
                            $new_data['add_time'] = $info['add_time'];
                            $new_data['amount'] = $info['amount'];
                            $new_data['remark'] = '盈诚游戏交易号【trade_no】:' . $orderNumber;
                            $new_data['money'] = $info['money'];
                            $new_data['pay_ip'] = $info['pay_ip'];
                            $new_data['order_id'] = $info['order_id'];
                            $new_data['real_server_id'] = $info['real_server_id'];

                            $ret = Db::connect('db_config_main')->table('purchase_data')->insertGetId($new_data);
                            if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);

                            /* 新增或更新用户充值统计数据 */
                            $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                            //修改订单状态
                            $update['id'] = $info['id'];
                            $update['order_status'] = 1;
                            $update['remark'] = '盈诚游戏交易号【trade_no】:' . $orderNumber;
                            if (!PurchaseData::update($update)) {
                                Log::write("订单号【out_trade_no】" . $cpOrderNumber . "状态更新失败！！！");
                            }
                            //充值元宝(命令发送服务器)
                            test::webw_packet_purchase($info['server_id'], $ret);
                        }
                    }
                    // 提交事务
                    Db::commit();
                    echo 'SUCCESS';
                } catch (Exception $exception) {
                    Log::write('玩家充值事务回滚,exception:' . $exception);
                    Db::rollback();
                    echo 'FAILED';
                }
            }
        } else {
            Log::write("盈诚游戏支付回调参数错误!!!");
        }
    }


    public function test()
    {
        $cpOrderNumber   = "yC_429497734920211126164220";
        $verify  =  stristr(trim($cpOrderNumber), "YC_");
if($verify==true){
     var_dump( $verify );}
    }
    /*
 * 更新玩家充值统计数据
 * @param $user_id      用户ID
 */
    public function update_user_recharge_statistics($user_id, $money)
    {
        if (isset($user_id)) {
            $statisticsInfo = UserRechargeStatistics::where('user_id', '=', $user_id)->find();
            if ($statisticsInfo) {
                $data['user_id'] = $user_id;
                /* 判断数据表中的时间戳与当前时间戳的年份、月份是否一致 */
                if (date('y-m', $statisticsInfo['update_time']) == date('y-m', time())) {
                    $data['month_recharge'] = $statisticsInfo['month_recharge'] + $money;
                } else {
                    $data['month_recharge'] = $money;
                }
                $data['total_recharge'] = $statisticsInfo['total_recharge'] + $money;
                $data['update_time'] = time();
                $ret = UserRechargeStatistics::update($data);
                if (!$ret) {
                    Log::write("用户ID【${user_id}】,数据充值统计更新金额【${money}】失败!");
                    return false;
                }
                return true;
            } else {
                $data['user_id'] = $user_id;
                $data['month_recharge'] = $money;
                $data['total_recharge'] = $money;
                $data['update_time'] = time();
                $ret = UserRechargeStatistics::insert($data);
                if (!$ret) {
                    Log::write("用户ID【${user_id}】,数据充值统计插入金额【${money}】失败!");
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * 解密方法
     * $strEncode 密文
     * $keys 解密密钥 为游戏接入时分配的 callback_key
     * @param $strEncode
     * @param $keys
     * @return string
     */
    public function decode($strEncode, $keys)
    {
        if (empty($strEncode)) {
            return $strEncode;
        }
        preg_match_all('(\d+)', $strEncode, $list);
        $list = $list[0];
        if (count($list) > 0) {
            $keys = self::getBytes($keys);
            for ($i = 0; $i < count($list); $i++) {
                $keyVar = $keys[$i % count($keys)];
                $data[$i] = $list[$i] - (0xff & $keyVar);
            }
            return self::toStr($data);
        } else {
            return $strEncode;
        }
    }


    /**
     * 计算游戏同步签名
     * @param $params
     * @param $callbackkey
     * @return string
     */
    public static function getSign($params, $callbackkey)
    {
        return md5($params['nt_data'] . $params['sign'] . $callbackkey);
    }

    /**
     * MD5签名替换
     */
    static private function replaceMD5($md5)
    {
        strtolower($md5);
        $bytes = self::getBytes($md5);

        $len = count($bytes);

        if ($len >= 23) {
            $change = $bytes[1];
            $bytes[1] = $bytes[13];
            $bytes[13] = $change;

            $change2 = $bytes[5];
            $bytes[5] = $bytes[17];
            $bytes[17] = $change2;

            $change3 = $bytes[7];
            $bytes[7] = $bytes[23];
            $bytes[23] = $change3;
        } else {
            return $md5;
        }

        return self::toStr($bytes);
    }

    /**
     * 转成字符数据
     * @param $string
     * @return array
     */
    private static function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    /**
     * 转化字符串
     * @param $bytes
     * @return string
     */
    private static function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }


    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_data($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
}
