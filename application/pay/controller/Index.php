<?php

namespace app\pay\controller;

use app\admin\model\PayConfig;
use app\admin\model\SwitchSet;
use app\common\test;
use app\pay\common\WxPayUtil;
use app\pay\model\RechargeData;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\View;
use app\pay\common;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/service/AlipayTradeService.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . './config.php';


class Index
{
    /**
     * @param $server_id    服务器ID
     * @param $user_id      用户ID
     * @param $recharge_id  充值ID
     * @param $type         充值类型
     * @param $old_server_id
     * @return string|\think\response\Json
     */
    public function index($server_id, $user_id, $recharge_id, $type, $old_server_id)
    {
        $switch = SwitchSet::where('id', '=', 1)->value('recharge_switch');
        if ($switch) 
        {
            if (isset($user_id) && isset($recharge_id) && isset($type))
            {
                $info = $this->get_recharge_by_csv($recharge_id, $type);
                if (empty($info)) 
                {
                    return json(['code' => -1, 'msg' => '未匹配到对应的充值信息']);
                }
                $money = isset($info['money']) ? $info['money'] : 0;
                $amount = $info['amount'];
                $relation_id = $info['relation_id'];

                View::assign([
                    'server_id' => $server_id,
                    'user_id' => $user_id,
                    'recharge_id' => $recharge_id,
                    'amount' => $amount,
                    'money' => $money,
                    'relation_id' => $relation_id,
                    'type' => $type,
                    'real_server_id' => $old_server_id
                ]);
            }
            else 
            {
                return json(['code' => -1, 'msg' => '参数错误']);
            }
            return View::fetch();
        }
//        return redirect('../../../404');
    }

    /**
     * 通过recharge_id与type获取充值信息
     * @param $recharge_id  充值ID
     * @param $type         充值类型
     * @return array
     */
    public function get_recharge_by_csv($recharge_id, $type)
    {
        /* $file_name = '../public/csv/recharge.csv';
        $file_open = fopen($file_name, 'r');
        // $data = fgetcsv($file_open, 100, ',');
        $count = 1;
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open))
        {
            if (!empty($data) && $count >= 1) 
            {
                for ($i = 0; $i < count($data); $i++) 
                {
                    if ($data[0] == $recharge_id && $data[1] == $type)
                    {
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
        fclose($file_open); */
        $where = [ ['Id', '=', $recharge_id], ['Type', '=', $type] ];
        $item = db('','db_table_config')->table('Recharge')->where($where)->field('RMB AS money,Gold AS amount,CorrespondenceForm AS relation_id')->find();
        return $item;
    }

    /**
     * 微信统一下单
     */
    public function unified_order()
    {
        $wxUtil = new WxPayUtil();
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $params = array();
        $params['body'] = "购买异玩平台虚拟币";
        $params['appid'] = config('pay.wx_pay.app_id');
        $params['mch_id'] = config('pay.wx_pay.merchantId');
        //$params['description'] = "购买异玩平台虚拟币";
        $params['total_fee'] = 1;
        $params['out_trade_no'] = 'YW_' . date('YmdHis');
        $params['notify_url'] = config('pay.wx_pay.notify_url');
        $params['nonce_str'] = $this->genRandomString();
        $total = 1;
        $params['amount'] = '{"amount":{"total":' . $total . ',"currency":"CNY"}}';
        $ip = request()->ip();
        $params['trade_type'] = 'MWEB';
        //$params['scene_info'] = '{"scene_info":{"payer_client_ip":' . $ip . ',"h5_info":{"type":"Wap","app_name":"龙腾天下"}}}';
        $params['scene_info'] = '{"scene_info":{"payer_client_ip":' . $ip . ',"h5_info":{"type":"Wap","wap_url":"http://52yiwan.cn","app_name":"龙腾天下"}}}';
        //获取签名数据
        $params['sign'] = $wxUtil->MakeSign($params); //签名

        $xml = $wxUtil->ToXml($params);
        Log::write("post xml str:" . $xml);
        $response = $wxUtil->postXmlCurl($url, $xml);
        if (!$response) {
            return false;
        }

        /**
         * <xml><return_code><![CDATA[SUCCESS]]></return_code>
         * <return_msg><![CDATA[OK]]></return_msg>
         * <result_code><![CDATA[SUCCESS]]></result_code>
         * <mch_id><![CDATA[1607251812]]></mch_id>
         * <appid><![CDATA[wx6e7075d4b448646f]]></appid>
         * <nonce_str><![CDATA[d8xGW86G38YAZM0X]]></nonce_str>
         * <sign><![CDATA[1C2E3D67F5868C288FE3A693CF375A94]]></sign>
         * <prepay_id><![CDATA[wx300938224735257a61dd6c1f12d4c10000]]></prepay_id>
         * <trade_type><![CDATA[MWEB]]></trade_type>
         * <mweb_url><![CDATA[https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx300938224735257a61dd6c1f12d4c10000&package=1148742901]]></mweb_url>
         * </xml>
         **/

        $result = $wxUtil->xml_to_data($response);
        if (!empty($result['result_code']) && !empty($result['err_code'])) {
            $result['err_msg'] = $this->error_code($result['err_code']);
        }

        if ($result['result_code'] == 'SUCCESS' && $result['return_msg'] == 'OK') {
            //发起微信支付url
            $pay_url = $result['mweb_url'] . '&redirect_url=' . urlencode($params['notify_url']);
            //数据库操作

            //返回发起支付url，微信外浏览器访问
            return $pay_url;
            // header("Location:{$pay_url}");
        }
    }

    /**
     * 提交充值操作
     */
    public function recharge()
    {
        $data = \request()->param();
        if (!isset($data) || empty($data)) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }
        $pay_type = intval($data['radio1']);
        //http://121.43.135.21/pay.php/index/recharge.html?radio1=2&amount=5000&user_id=4294977318&server_id=1&type=1&recharge_id=2&money=50

        $payInfo = PayConfig::where([['status', '=', 1], ['id', '=', 2]])->find();

        $user_id = $data['user_id'];
        $total_amount = $data['money'];

        //支付方式（0渠道支付，1支付宝支付，2微信支付，3汇付宝）
        $pay_way = 0;
        if ($pay_type == 2) {
            //支付宝官方支付订单编号
            $out_trade_no = 'ZFB_' . $user_id . date('YmdHis');//订单编号格式（YW_+用户id+YmdHis）
            $pay_way = 1;

        } elseif ($pay_type == 1) {
            if ($payInfo) {
                //微信官方支付订单编号
                $out_trade_no = 'WX_' . $user_id . date('YmdHis');//订单编号格式（YW_+用户id+YmdHis）
                $pay_way = 2;
            } else {
                //汇付宝支付订单编号
                $out_trade_no = 'HP_' . $user_id . date('YmdHis');//订单编号格式（YW_+用户id+YmdHis）
                $pay_way = 3;
            }
        } else {
            //内部账号测试订单编号
            $out_trade_no = 'YW_' . $user_id . date('YmdHis');;
        }
        $new_data['user_id'] = $user_id;
        $new_data['server_id'] = $data['server_id'];
        $new_data['recharge_id'] = $data['recharge_id'];
        $new_data['channel_id'] = $data['type'];
        $new_data['pay_type'] = $pay_way;
        $new_data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        $new_data['amount'] = $data['amount'];
        $new_data['money'] = $data['money'];
        $new_data['pay_ip'] = request()->ip();
        $new_data['order_id'] = $out_trade_no;
        $new_data['real_server_id'] = $data['old_server_id'];

        //启动事务
        Db::startTrans();
        try {
            $ret = RechargeData::insertGetId($new_data);
            if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);
            // 提交事务
            Db::commit();
            //radio1==1 微信支付；radio1==2 支付宝支付
            if ($pay_type == 1) {
                //微信支付发起 id=2 为微信支付且支付状态开启
                if ($payInfo) {
                    $this->wx_pay($out_trade_no, $total_amount);
                } else {
                    //id=1 汇付宝支付渠道
                    HeePay::index($out_trade_no, $total_amount);
                }
            } else if ($pay_type == 2) {
                //支付宝支付发起 TODO：
                $this->zfb_pay($out_trade_no, $total_amount);
            } else {
                //其他支付方式(测试专用) TODO：
                $local_recharge_ret = Db::connect('db_config_main', true)->table('recharge_data')->insertGetId($new_data);
                test::webw_packet_recharge($data['server_id'], $local_recharge_ret);
            }
        } catch (\Exception $exception) {
            Log::write('玩家充值事务回滚,exception:' . $exception);
            Db::rollback();
            return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);
        }
        return View::fetch('success');
    }

    /**
     * 微信支付跳转
     * @param $out_trade_no 订单号
     * @param $total_amount 充值金额
     * @return false
     */
    public function wx_pay($out_trade_no, $total_amount)
    {
        $wxUtil = new WxPayUtil();
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $params = array();
        $params['body'] = "购买异玩平台虚拟币";
        $params['appid'] = config('pay.wx_pay.app_id');
        $params['mch_id'] = config('pay.wx_pay.merchantId');
        //$params['description'] = "购买异玩平台虚拟币";
        $params['total_fee'] = $total_amount * 100;
        $params['out_trade_no'] = $out_trade_no;
        $params['notify_url'] = config('pay.wx_pay.notify_url');
        $params['nonce_str'] = $this->genRandomString();
        $params['amount'] = '{"amount":{"total":' . $total_amount . ',"currency":"CNY"}}';
        $ip = $this->get_client_ip();//request()->ip();
        $params['spbill_create_ip'] = $ip;

        $params['trade_type'] = 'MWEB';
        $params['scene_info'] = '{"scene_info":{"payer_client_ip":' . $ip . ',"h5_info":{"type":"Wap","wap_url":"http://52yiwan.cn","app_name":"龙腾天下"}}}';

        //获取签名数据
        $params['sign'] = $wxUtil->MakeSign($params); //签名

        $xml = $wxUtil->ToXml($params);
        $response = $wxUtil->postXmlCurl($url, $xml);
        if (!$response) {
            return false;
        }

        $result = $wxUtil->xml_to_data($response);

        if (!empty($result['result_code']) && !empty($result['err_code'])) {
            $result['err_msg'] = $this->error_code($result['err_code']);
        }

        if ($result['result_code'] == 'SUCCESS' && $result['return_msg'] == 'OK') {
            //发起微信支付url
            $pay_url = $result['mweb_url'] . '&redirect_url=' . urlencode($params['notify_url']);
            //数据库操作
            self::redirect($pay_url);
        }
    }

    /**
     * 获取客户端IP
     */
    function get_client_ip()
    {
        $client_ip = "unknown";
        if ($_SERVER['REMOTE_ADDR']) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $client_ip = getenv("REMOTE_ADDR");
        }
        return $client_ip;
    }

    /**
     * 微信支付异步通知回调
     */
    public function notify()
    {
        Log::write("微信支付异步通知开始！！！！");
        //接受微信回调的参数
        $xmlData = file_get_contents('php://input');

        $wxUtil = new WxPayUtil();
        $result = $wxUtil->xml_to_data($xmlData);

        if ($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') {
            $out_trade_no = $result['out_trade_no'];
            $transaction_id = $result['transaction_id'];
            //启动事务
            Db::startTrans();
            try {
                $info = RechargeData::where('order_id', '=', trim($out_trade_no))->find();
                //if ($info && $info['status'] == 0) {
                if ($info && $info['order_status'] == 0) {
                    $new_data['user_id'] = $info['user_id'];
                    $new_data['server_id'] = $info['server_id'];
                    $new_data['recharge_id'] = $info['recharge_id'];
                    $new_data['pay_type'] = $info['pay_type'];
                    $new_data['channel_id'] = $info['channel_id'];
                    $new_data['add_time'] = $info['add_time'];
                    $new_data['amount'] = $info['amount'];
                    $new_data['remark'] = '微信官方支付交易号【transaction_id】:' . $transaction_id;
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
                    $update['remark'] = '微信官方支付交易号【transaction_id】:' . $transaction_id;
                    if (!RechargeData::update($update)) {
                        Log::write("订单号【out_trade_no】" . $out_trade_no . "状态更新失败！！！");
                    }
                    //充值元宝(命令发送服务器)
                    test::webw_packet_recharge($info['server_id'], $ret);
                }

                // 提交事务
                Db::commit();

                return View::fetch('index/success');

            } catch (Exception $exception) {
                Log::write('玩家充值事务回滚,exception:' . $exception);
                Db::rollback();
            }
        }

        /**
         * <xml><appid><![CDATA[wx6e7075d4b448646f]]></appid>
         * <bank_type><![CDATA[ABC_DEBIT]]></bank_type>
         * <cash_fee><![CDATA[10]]></cash_fee>
         * <fee_type><![CDATA[CNY]]></fee_type>
         * <is_subscribe><![CDATA[N]]></is_subscribe>
         * <mch_id><![CDATA[1607251812]]></mch_id>
         * <nonce_str><![CDATA[JgDknv4wrwOnvMmOObIpUOwkzII4tLpw]]></nonce_str>
         * <openid><![CDATA[oAyx5ji6Pan1HNch1yZHaWNtJ8jU]]></openid>
         * <out_trade_no><![CDATA[WX_2147484648120210406181750]]></out_trade_no>
         * <result_code><![CDATA[SUCCESS]]></result_code>
         * <return_code><![CDATA[SUCCESS]]></return_code>
         * <sign><![CDATA[431924A3A49E6B097569F1D154273EA6]]></sign>
         * <time_end><![CDATA[20210406181801]]></time_end>
         * <total_fee>10</total_fee>
         * <trade_type><![CDATA[MWEB]]></trade_type>
         * <transaction_id><![CDATA[4200000945202104065644818280]]></transaction_id>
         **/


        Log::write("微信支付异步通知xmlData:" . $xmlData);
        Log::write("微信支付异步通知结束！！！！");
    }


    /**
     * 支付宝支付跳转
     * @param $out_trade_no
     * @param $total_amount
     * @throws \Exception
     */
    public function zfb_pay($out_trade_no, $total_amount)
    {
        if (!empty($out_trade_no) && trim($out_trade_no) != "") {
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $out_trade_no;
            //订单名称，必填
            $subject = "平台元宝充值";
            //付款金额，必填
            $total_amount = $total_amount;
            //商品描述，可空
            $body = $total_amount . "元元宝";
            //超时时间
            $timeout_express = "1m";

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setTimeExpress($timeout_express);

            $payResponse = new \AlipayTradeService(config('pay.ali_pay'));
            $result = $payResponse->wapPay($payRequestBuilder, config('pay.ali_pay.return_url'), config('pay.ali_pay.notify_url'));
            Log::write("支付宝支付返回结果:" . $result);
            return;
        }
    }


    /**
     * 支付宝支付异步通知回调
     * 支付宝支付回调接口
     */
    public function zfb_notify_url()
    {
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService(config('pay.ali_pay'));
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        $alipaySevice->writeLog($result);
        if ($result) {
            //商户订单号
            $out_trade_no = $arr['out_trade_no'];
            //支付宝交易号
            $trade_no = $arr['trade_no'];
            //交易状态
            $trade_status = $arr['trade_status'];

            Log::write("支付宝支付回调接口返回out_trade_no：" . $out_trade_no);
            Log::write("支付宝支付回调接口返回trade_status：" . $trade_status);
            if ($trade_status == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($trade_status == 'TRADE_SUCCESS') {
                //启动事务
                Db::startTrans();
                try {
                    $info = RechargeData::where('order_id', '=', trim($out_trade_no))->find();
                    if ($info && $info['order_status'] == 0) {
                        //$new_data['id'] = $info['id'];
                        $new_data['user_id'] = $info['user_id'];
                        $new_data['server_id'] = $info['server_id'];
                        $new_data['recharge_id'] = $info['recharge_id'];
                        $new_data['pay_type'] = $info['pay_type'];
                        $new_data['channel_id'] = $info['channel_id'];
                        $new_data['add_time'] = $info['add_time'];
                        $new_data['amount'] = $info['amount'];
                        $new_data['remark'] = '支付宝交易号【trade_no】:' . $trade_no;
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
                        $update['remark'] = '支付宝交易号【trade_no】:' . $trade_no;
                        if (!RechargeData::update($update)) {
                            Log::write("订单号【out_trade_no】" . $out_trade_no . "状态更新失败！！！");
                        }
                        //充值元宝(命令发送服务器)
                        test::webw_packet_recharge($info['server_id'], $ret);
                    }

                    // 提交事务
                    Db::commit();
                } catch (Exception $exception) {
                    Log::write('玩家充值事务回滚,exception:' . $exception);

                    //特殊处理 start
                    $this->special_handle_order($out_trade_no, $trade_no);
                    //特殊处理 end

                    Db::rollback();
                }
            }
            echo "success";
        } else {
            echo "fail";
        }
    }

    /**
     * 数据库连接超时特殊处理
     * @param $order_id  平台订单号
     * @param $trade_no  第三方平台交易号
     */
    public function special_handle_order($order_id, $trade_no)
    {
        Db::transaction(function () use ($order_id, $trade_no) {
            $info = RechargeData::where('order_id', '=', $order_id)->find();
            if ($info && $info['order_status'] == 0) {
                $new_data['user_id'] = $info['user_id'];
                $new_data['server_id'] = $info['server_id'];
                $new_data['recharge_id'] = $info['recharge_id'];
                $new_data['pay_type'] = $info['pay_type'];
                $new_data['channel_id'] = $info['channel_id'];
                $new_data['add_time'] = $info['add_time'];
                $new_data['amount'] = $info['amount'];
                if ($info['pay_type'] == 1) {
                    $remark = '支付宝交易号【trade_no】:' . $trade_no;
                } else {
                    $remark = '微信交易号【trade_no】:' . $trade_no;
                }
                $new_data['remark'] = $remark;
                $new_data['money'] = $info['money'];
                $new_data['pay_ip'] = $info['pay_ip'];
                $new_data['order_id'] = $order_id;
                $new_data['real_server_id'] = $info['real_server_id'];

                //插入数据到服务端数据表（**_main.recharge_data）
                $ret = Db::connect('db_config_main')->table('recharge_data')->insert($new_data);
                if (!$ret) {
                    return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);
                }
                //插入或更新用户充值统计数据信息
                $user_id = $info['user_id'];
                $user_info = UserRechargeStatistics::where('user_id', '=', $user_id)->find();
                if ($user_info) {
                    $user_recharge['user_id'] = $user_id;
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
                $update['remark'] = '支付宝交易号【trade_no】:' . $trade_no;
                if (!RechargeData::update($update)) {
                    Log::write("订单号【out_trade_no】" . $order_id . "状态更新失败！！！");
                }
                //充值元宝(命令发送服务器)
                test::webw_packet_recharge($info['server_id'], $ret);
            }
        });
    }

    /**
     * 支付宝支付同步通知回调
     */
    public function return_url()
    {
        $arr = $_GET;
        $alipaySevice = new \AlipayTradeService(config('pay.ali_pay'));
        $result = $alipaySevice->check($arr);
        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号

            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            //支付宝交易号

            $trade_no = htmlspecialchars($_GET['trade_no']);

            return View::fetch('index/success');

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            echo "验证失败";
        }
    }

    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param type $len 产生字符串的长度
     * @return string 随机字符串
     */
    public function genRandomString($len = 32)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    /**
     * 支付成功跳转页面
     */
    public function success()
    {
        return View::fetch();
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
