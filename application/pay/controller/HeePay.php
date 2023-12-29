<?php

namespace app\pay\controller;


use app\common\test;
use app\pay\common\WxPayUtil;
use app\pay\model\RechargeData;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\View;

/**
 * 汇付宝支付渠道
 */
class HeePay
{
    public static function index($out_trade_no, $amount)
    {
        /**
         * 原始数据：meta_option={"s":"WAP","n":"京东官网","id":"https://m.jd.com"}
         * 加密后结果：meta_option=eyJzIjoiV0FQIiwibiI6Ir6ptqu52c34IiwiaWQiOiJodHRwczovL20uamQuY29tIn0%3d
         *                        eyJzIjoiV0FQIiwibiI6Ir6ptqu52c34IiwiaWQiOiJodHRwczovL20uamQuY29tIn0%3D
         **/
        $pay_url = config('pay.hee_pay.pay_url');
        $notify_url = config('pay.hee_pay.notify_url');
        $return_url = config('pay.hee_pay.return_url');

        $version = 1;
        $scene = 'h5';
        $pay_type = 30;
        $agent_id = config('pay.hee_pay.agent_id');
        $agent_bill_id = $out_trade_no;// 'HP_' . $user_id . date('YmdHis');//
        $agent_bill_time = date('YmdHis');//20210319171721
        $pay_amt = $amount; //订单金额
        $ip = request()->ip();
        $user_ip = str_replace('.', '_', $ip);
        $goods_name = urlencode(iconv('UTF-8', 'GBK', '平台元宝充值'));
        $remark = '';
        $payment_mode = 'cashier';
        $meta_option = '{"s":"WAP","n":"龙腾天下","id":"https://52yiwan.cn"}';
        $meta_option = iconv("UTF-8", "GBK", $meta_option);
        $meta_option = urlencode(base64_encode($meta_option));
        $bank_card_type = -1;
        $sign_type = "MD5";

        $sign_str = '';
        $sign_str .= 'version=' . $version;
        $sign_str .= '&agent_id=' . $agent_id;
        $sign_str .= '&agent_bill_id=' . $agent_bill_id;
        $sign_str .= '&agent_bill_time=' . $agent_bill_time;
        $sign_str .= '&pay_type=' . $pay_type;
        $sign_str .= '&pay_amt=' . $pay_amt;
        $sign_str .= '&notify_url=' . $notify_url;
        $sign_str .= '&return_url=' . $return_url;
        $sign_str .= '&user_ip=' . $user_ip;
        $sign_str .= '&bank_card_type=' . $bank_card_type;
        $sign_str .= '&remark=' . $remark;
        $sign_str .= '&key=' . config('pay.hee_pay.sign_key');
        $sign = md5($sign_str);

        /** 拼接请求参数 **/
        $param = '';
        $param .= 'version=' . $version;
        $param .= '&scene=' . $scene;
        $param .= '&pay_type=' . $pay_type;
        $param .= '&agent_id=' . $agent_id;
        $param .= '&agent_bill_id=' . $agent_bill_id;
        $param .= '&agent_bill_time=' . $agent_bill_time;
        $param .= '&pay_amt=' . $pay_amt;
        $param .= '&notify_url=' . $notify_url;
        $param .= '&return_url=' . $return_url;
        $param .= '&user_ip=' . $user_ip;
        $param .= '&goods_name=' . $goods_name;
        $param .= '&remark=' . $remark;
        $param .= '&payment_mode=' . $payment_mode;
        $param .= '&meta_option=' . $meta_option;
        $param .= '&bank_card_type=' . $bank_card_type;
        $param .= '&sign_type=' . $sign_type;
        $param .= '&sign=' . $sign;


        $url = $pay_url . '?' . $param;
        $html = file_get_contents($url);


        //xml转数组
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($html, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        var_dump($data);

        if (array_key_exists('ret_code', $data)) {
            if ($data['ret_code'] == "0000") {
                self::redirect($data['redirectUrl']);
            } else {
                return json(['code' => $data['ret_code'], 'msg' => $data['ret_msg']]);
            }
        }

        /**
         *   'ret_code' => string '0000' (length=4)
         * 'ret_msg' => string '创建支付单成功' (length=21)
         * 'redirectUrl' => string 'https://hykjh5.heemoney.com/DirectPay/WxPayment.aspx?stid=H21032335976261C_402ca97f524b1bddbb2162a3dda52419' (length=107)
         * 'sign' => string '324f5469b0a88d9b3a684fe2a9107da4' (length=32)
         **/
    }

    /**
     * 汇付宝支付异步通知回调
    **/
    public function notify_url()
    {
        /**
         *
         * 汇付宝notify_url接收到的参数结果:
         * heepay notify_url result:1
         * heepay notify_url pay_message:
         * heepay notify_url agent_id:2126136
         * heepay notify_url jnet_bill_no:H21033145609751C
         * heepay notify_url agent_bill_id:HP_429497732220210331111911
         * heepay notify_url pay_type:30
         * heepay notify_url pay_amt:0.01
         * heepay notify_url remark:
         * heepay notify_url sign:f96c2a748d7d65fbcf78540bd278beba
         * ---------------------------------------------------------------
         **/
        $result = $_GET['result'];
        $pay_message = $_GET['pay_message'];
        $agent_id = $_GET['agent_id'];
        $jnet_bill_no = $_GET['jnet_bill_no'];
        $agent_bill_id = $_GET['agent_bill_id'];
        $pay_type = $_GET['pay_type'];
        $pay_amt = $_GET['pay_amt'];
        $remark = $_GET['remark'];
        $return_sign = $_GET['sign'];

        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark));//签名验证中的中文采用UTF-8编码;

        $signStr = '';
        $signStr .= 'result=' . $result;
        $signStr .= '&agent_id=' . $agent_id;
        $signStr .= '&jnet_bill_no=' . $jnet_bill_no;
        $signStr .= '&agent_bill_id=' . $agent_bill_id;
        $signStr .= '&pay_type=' . $pay_type;
        $signStr .= '&pay_amt=' . $pay_amt;
        $signStr .= '&remark=' . $remark;
        $signStr .= '&key=' . config('pay.hee_pay.sign_key'); //商户签名密钥

        $sign = '';
        $sign = md5($signStr);
        if ($sign == $return_sign) {
            //比较签名密钥结果是否一致，一致则保证了数据的一致性
            echo 'ok';
            //启动事务
            Db::startTrans();
            try {
                $info = RechargeData::where('order_id', '=', trim($agent_bill_id))->find();
                if ($info && $info['order_status'] == 0) {
                    $new_data['user_id'] = $info['user_id'];
                    $new_data['server_id'] = $info['server_id'];
                    $new_data['recharge_id'] = $info['recharge_id'];
                    $new_data['pay_type'] = $info['pay_type'];
                    $new_data['channel_id'] = $info['channel_id'];
                    $new_data['add_time'] = $info['add_time'];
                    $new_data['amount'] = $info['amount'];
                    $new_data['remark'] = '汇付宝交易号【jnet_bill_no】:' . $jnet_bill_no;
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
                    $update['remark'] = '汇付宝交易号【trade_no】:' . $jnet_bill_no;
                    if (!RechargeData::update($update)) {
                        Log::write("订单号【out_trade_no】" . $agent_bill_id . "状态更新失败！！！");
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
            Log::write("汇付宝支付完毕返回OK");

            //商户自行处理自己的业务逻辑
        } else {
            Log::write("汇付宝支付完毕返回ERROR");
            echo 'error';
            //商户自行处理，可通过查询接口更新订单状态，也可以通过商户后台自行补发通知，或者反馈运营人工补发
        }
    }

    /**
     * 同步回调地址
    */
    public function return_url()
    {
        $result = $_GET['result'];
        $pay_message = $_GET['pay_message'];
        $agent_id = $_GET['agent_id'];
        $jnet_bill_no = $_GET['jnet_bill_no'];
        $agent_bill_id = $_GET['agent_bill_id'];
        $pay_type = $_GET['pay_type'];
        $pay_amt = $_GET['pay_amt'];
        $remark = $_GET['remark'];
        $return_sign = $_GET['sign'];

        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark));//签名验证中的中文采用UTF-8编码;

        $signStr = '';
        $signStr .= 'result=' . $result;
        $signStr .= '&agent_id=' . $agent_id;
        $signStr .= '&jnet_bill_no=' . $jnet_bill_no;
        $signStr .= '&agent_bill_id=' . $agent_bill_id;
        $signStr .= '&pay_type=' . $pay_type;
        $signStr .= '&pay_amt=' . $pay_amt;
        $signStr .= '&remark=' . $remark;
        $signStr .= '&key=' . config('pay.hee_pay.sign_key'); //商户签名密钥

        $sign = '';
        $sign = md5($signStr);
        if ($sign == $return_sign) {
            return redirect('/pay.php/index/success');
        }
    }

    /**
     * 地址重定向
     * @param $url
     */
    static function redirect($url)
    {
        header("Location: $url");
        exit();
    }
}
