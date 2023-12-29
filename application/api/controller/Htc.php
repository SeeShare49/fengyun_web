<?php

namespace app\api\controller;

use app\admin\model\RechargeData;
use app\common\test;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

header("Content-type: application/x-tar; charset=utf-8");
include_once 'joloRsa.php';

/**
 * 聚乐支付接口
 **/
class Htc
{
    public function index()
    {
        //local sRechargeUrl = ORDER_URL .. "server_id=" .. nServerId .. "&user_id=" .. nActorId .. "&recharge_id=" ..
        // nRechargeId .. "&type=" .. nRechargeType.."&old_server_id="..nOldServerId.."&nChanneId="..nChanneId

        Log::write("聚乐支付请求来袭....");
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
                $money = isset($info['money']) ? $info['money'] : 0;
                $amount = $info['amount'];
            }

            $data['user_id'] = $user_id;
            $data['server_id'] = $param['server_id'];
            $data['recharge_id'] = $recharge_id;
            $data['amount'] = $amount;
            $data['money'] = $money;
            $money = 1;
            $data['pay_type'] = 1;//对应支付配置表ID（guild3）
            $data['order_id'] = 'JL_' . $user_id . date('YmdHis');//渠道ID_+用户ID+日期、
            $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
            $data['channel_id'] = $channel_id;
            $data['pay_ip'] = request()->ip();
            $data['real_server_id'] = $param['old_server_id'];
            if (\app\pay\model\RechargeData::insertGetId($data)) {
                return json(['code' => true, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '订单创建成功']);
            } else {
                return json(['code' => false, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '订单创建失败']);
            }

        } else {
            return json(['code' => false, 'msg' => '参数错误']);
        }
    }

    /**
     * 聚乐同步回调地址
     **/
    public function notify_url_1()
    {
        Log::write(time() . "聚乐回调该接口了！！！！");
        $data = Request::post();
        Log::write("聚乐同步回调参数是否为空:" . empty($data));

        Log::write("request param start:");
        dump(Request::param());
        Log::write(Request::param());
        Log::write("request param end:");


        Log::write("request post start:");
        dump(Request::post());
        Log::write(Request::post());
        Log::write("request post end:");

        /**
         * AppSecret 由聚乐提供的rsa公钥
         */
        $AppSecret = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDbRLzWfCD4pQb1mjeGLy6gw+AfOKZ1dpNbMUyZml+p3stTSdTyHHpkuPPsaOqsT9gFDSmXz5KRBt4w6KCeLj/R61KA5rmMJipDnSJV19kld0z6NW47kiEQHslaalDBCST94TUIcCzjhaiG3yTChDCTFo3v47qyt6j3YvVpih8UNQIDAQAB';

        /**
         *$input>>>->
         * order="%7B%22result_code%22%3A1%2C%22gmt_create%22%3A%222018-07-30+21%3A08%3A02%22%2C%22real_amount%22%3A2900%2C%22result_msg%22%3A%22%E6%94%AF%E4%BB%98%E6%88%90%E5%8A%9F%22%2C%22game_code%22%3A%222922648688028%22%2C%22game_order_id%22%3A%2200208849488%24gift_failed_1_ol%22%2C%22jolo_order_id%22%3A%22ZF51209c62c3c34236ba57c9f362c93dd2%22%2C%22gmt_payment%22%3A%222018-07-30+21%3A09%3A22%22%7D"&sign="hJf%2BuupY6nO49DSUf%2BLtXssJu0ONDwA1aUndO7ixS2eX8ThyQXC1%2FPM9havtEEzG9XeZiN9Cy%2BRcFx5u3z7BBJmNxyUt27dskaP2swSlgcKz60%2BKpRN8jTxbvY%2BDSMfG3C2i8TFi37RCo0uLofFAdQL3%2FsZhE3i3q9lwxHpsLEk%3D"&sign_type="RSA"
         *
         */
        $input = file_get_contents("php://input");//$GLOBALS['HTTP_RAW_POST_DATA'];
        parse_str($input, $data);
        if (!isset($data['order']) || !isset($data['sign'])) {
            return "fail0 data error";
        }
        $order = substr(urldecode($data['order']), 1, -1);
        $sign = $data['sign'];

        $rsa = new \joloRsa();
        $pub_key = $rsa->setupPubKey($AppSecret);
        $dataStatus = $rsa->rsa_verify($order, $sign, $pub_key);
        if (!$dataStatus) {
            return "fail2 data invalid";
        }
        $orderData = json_decode($order, true);
        Log::write("order data:");
        $result_code = $orderData['result_code']; //1代表支付成功 0代表支付失败
        $gmt_create = $orderData['gmt_create']; //支付订单创建时间 格式：yyyy-MM-dd HH:mm:ss;
        $amount = $orderData['real_amount']; //用户真实付费金额，单位为分
        $result_msg = $orderData['result_msg']; //支付结果提示语，成功=支付成功；失败=支付失败
        $game_code = $orderData['game_code']; //游戏编号
        $game_order_id = $orderData['game_order_id']; //游戏订单id
        $orderId = $orderData['jolo_order_id']; //jolo支付订单id
        $gmt_payment = $orderData['gmt_payment']; //支付时间


        Log::write('result code:' . $result_code);
        Log::write('gmt_create:' . $gmt_create);
        Log::write('real_amount:' . $amount);
        Log::write('result msg:' . $result_msg);
        Log::write('game code:' . $game_code);
        Log::write('game order id:' . $game_order_id);
        Log::write('orderId:' . $orderId);
        Log::write('gmt payment:' . $gmt_payment);

        if ($result_code != 1) {
            return "fail3 data pay invalid";
        }
        echo "success";
        return "success";

    }


    public function notify_url()
    {
        //$result = \think\facade\Request::param();
        $result = array (
            'nt_data' => '@114@116@171@159@157@86@175@155@163@172@159@168@165@116@85@104@101@104@82@80@151@167@156@163@155@161@162@153@115@90@135@135@124@98@107@84@81@169@173@151@159@157@151@165@166@165@152@116@89@166@159@82@113@119@117@165@172@161@151@157@169@156@157@146@163@154@166@165@146@157@158@116@109@166@155@172@170@152@154@156@117@116@153@163@145@173@158@167@171@118@100@110@101@161@165@146@170@154@166@166@111@114@156@158@146@167@164@158@163@117@100@106@104@116@95@147@154@154@167@162@156@164@114@110@153@160@147@161@164@154@159@145@159@151@166@155@111@117@101@156@159@152@161@165@156@164@143@158@147@166@158@114@115@155@156@147@164@166@151@159@149@170@156@150@111@103@105@107@104@110@103@112@107@112@111@102@154@160@145@158@160@158@165@147@172@161@152@112@114@155@154@148@164@163@152@158@144@165@171@154@150@171@116@147@125@104@99@108@110@109@97@103@102@114@105@103@155@109@108@149@104@105@101@149@152@106@106@151@151@104@117@101@148@161@151@167@165@156@159@150@166@170@148@149@164@119@117@155@152@165@153@145@165@170@150@152@168@115@125@126@144@106@107@111@101@114@109@112@106@103@101@105@103@106@97@96@105@105@114@101@112@107@102@101@111@116@97@154@151@162@152@145@160@168@157@155@163@119@114@168@169@155@152@169@150@166@159@110@99@108@106@102@103@106@101@98@109@104@107@100@111@104@101@101@106@102@113@107@101@105@107@106@110@106@111@102@166@170@148@149@164@152@167@163@117@116@164@147@175@151@166@156@163@154@113@100@97@104@106@99@97@112@99@105@112@87@100@112@113@107@99@106@99@105@117@99@167@153@173@145@170@161@159@152@116@113@148@159@160@171@167@170@111@106@100@105@103@115@98@152@164@167@165@158@166@119@117@167@171@153@168@167@169@118@98@111@101@168@167@147@165@171@172@116@109@158@174@173@169@152@166@150@167@153@162@145@159@172@119@101@106@105@112@97@155@176@166@165@151@168@146@162@146@168@154@163@164@119@114@104@164@156@166@170@152@159@149@110@110@104@170@169@160@155@159@165@154@163@145@160@155@168@166@147@152@155@119',
            'sign' => '@106@101@100@147@97@105@158@102@146@111@108@113@104@155@149@106@112@111@147@150@106@110@107@152@107@154@100@147@111@155@149@108',
            'md5Sign' => 'abbbf12dcdab8b5d6b6aec465186c822',
        );
        if (isset($result)) {
            $call_back_key = '65321696196977377800299478426823';
            $response = $this->decode($result['nt_data'], $call_back_key);
            Log::write("response return xml:" . $response);
            $ret_str = $this->xml_to_data($response);
            var_dump("ret str:" . $ret_str);
            /**
             * <?xml version="1.0" encoding="UTF-8" standalone="no"?>
             * <quicksdk_message>
             * <message>
             * <is_test>1</is_test>
             * <channel>0</channel>
             * <channel_uid>fa820cc1ad39a4e99283e9fa555035ec@_()</channel_uid>
             * <channel_order></channel_order>
             * <game_order>1_429497729920210705174637</game_order>
             * <order_no>00020210705174638757160453</order_no>
             * <pay_time>2021-07-05 17:46:38</pay_time>
             * <amount>10.00</amount>
             * <status>0</status>
             * <extras_params></extras_params>
             * </message>
             * </quicksdk_message>
             **/

            Log::write("status:" . $ret_str['message']['status']);
            if ($ret_str['message']['status'] == "0") {
                $cpOrderNumber = $ret_str['message']['game_order'];
                $orderNumber = $ret_str['message']['order_no'];

                Log::write("cpOrderNumber:" . $cpOrderNumber);
                Log::write("orderNumber:" . $orderNumber);
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
                        $new_data['remark'] = '天宇游交易号【trade_no】:' . $orderNumber;
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
                        $update['remark'] = '天宇游交易号【trade_no】:' . $orderNumber;
                        if (!\app\pay\model\RechargeData::update($update)) {
                            Log::write("订单号【out_trade_no】" . $cpOrderNumber . "状态更新失败！！！");
                        }
                        //充值元宝(命令发送服务器)
                        test::webw_packet_recharge($info['server_id'], $ret);
                    }

                    // 提交事务
                    Db::commit();

                    echo 'success';

                } catch (Exception $exception) {
                    Log::write('玩家充值事务回滚,exception:' . $exception);
                    Db::rollback();
                    echo 'fail';
                }
            }
        } else {
            Log::write("天宇游支付回调参数错误!!!");
            echo 'fail';
        }
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
     * @param $md5
     * @return string
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
     * @return false|mixed
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
