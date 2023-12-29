<?php

namespace app\pay\controller;

use app\admin\model\PayConfig;
use app\admin\model\PurchaseData;
use app\common\test;
use app\pay\common\WxPayUtil;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

/*
 * RMB直购（直充）类
 */


require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/service/AlipayTradeService.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . './config.php';


class Purchase
{
    /*
     * RMB直购入口
     */
    public function index()
    {
        $param = Request::param();
        $server_id = $param['server_id'];
        $user_id = $param['user_id'];
        $channel_id = $param['channel_id'];
        $recharge_id = $param['recharge_id'];
        $old_server_id = $param['old_server_id'];
        if (isset($server_id) && isset($user_id) && isset($recharge_id) && isset($old_server_id)) {
            $info = $this->get_recharge_by_csv($recharge_id);
            if (empty($info)) {
                return json(['code' => -1, 'msg' => '未匹配到对应的直充配置信息']);
            }
            $money = isset($info['money']) ? $info['money'] : 0;
            $prop_name = $info['prop_name'];
            View::assign([
                'server_id' => $server_id,
                'user_id' => $user_id,
                'recharge_id' => $recharge_id,
                'prop_name' => $prop_name,
                'money' => $money,
                'channel_id'=>$channel_id,
                'real_server_id' => $old_server_id
            ]);
        } else {
            return json(['code' => -1, 'msg' => '参数错误,请核对参数信息']);
        }
        return View::fetch();
    }

    /*
     * 提交直充信息（创建直充订单）
     */
    public function submit_recharge()
    {
        $param = Request::param();
        if (empty($param)) {
            return json(['code' => -1, 'msg' => '直充参数错误,请核对参数信息']);
        }

        //获取微信（官方）充值配置信息
        $payConfigInfo = PayConfig::where([['status', '=', 1], ['id', '=', 2]])->find();

        //支付类型（微信、支付宝）
        $pay_type = intval($param['radio1']);
        $user_id = $param['user_id'];
        $total_amount =0.1;// $param['money'];
        $pay_way = 0;//支付方式（0渠道支付，1支付宝支付，2微信支付，3汇付宝）
        if ($pay_type == 2) {
            /*
             * 支付宝官方支付订单编号
             * 订单编号格式（YW_+用户id+YmdHis）
             */
            $out_trade_no = 'ZFB_' . $user_id . date('YmdHis');
            $pay_way = 1;
        } elseif ($pay_type == 1) {
            if ($payConfigInfo) {
                /*
                 * 微信官方支付订单编号
                 * 订单编号格式（YW_+用户id+YmdHis）
                 */
                $out_trade_no = 'WX_' . $user_id . date('YmdHis');
                $pay_way = 2;
            } else {
                /*
                 * 汇付宝支付订单编号
                 * 订单编号格式（YW_+用户id+YmdHis）
                 */
                $out_trade_no = 'HP_' . $user_id . date('YmdHis');
                $pay_way = 3;
            }
        } else {
            /** 内部账号测试订单编号 **/
            $out_trade_no = 'YW_' . $user_id . date('YmdHis');
        }

        $data = array();
        $data['user_id'] = $user_id;
        $data['server_id'] = $param['server_id'];
        $data['real_server_id'] = $param['old_server_id'];
        $data['recharge_id'] = $param['recharge_id'];
        $data['purchase_name'] = $param['prop_name'];
        $data['channel_id'] = $param['channel_id'];
        $data['order_id'] = $out_trade_no;
        $data['pay_type'] = $pay_way;
        $data['amount'] = 1;//默认直购数量1
        $data['money'] = $total_amount;
        $data['pay_ip'] = Request::ip();
        $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        $ret = $this->save_purchase_data($data);
        if (!$ret) {
            return json(['code' => -1, 'msg' => '直充订单数据提交失败,请重试!', 'data' => $data]);
        }
        if($pay_type==3){
            //其他支付方式(测试专用) TODO：
            $local_recharge_ret = Db::connect('db_config_main', true)->table('purchase_data')->insertGetId($data);
            test::webw_packet_purchase($data['server_id'], $local_recharge_ret);
        }else {
            /* 提交支付 */
            $this->submit_pay($pay_type, $total_amount, $out_trade_no, $payConfigInfo);
        }
    }

    /*
     * 保存直充订单数据
     * @param array $data
     * @return bool
     */
    public function save_purchase_data(array $data)
    {
        $ret = (new PurchaseData)->insertGetId($data);
        return isset($ret);
    }

    /*
     * 保存Main库直充订单数据
     */
    public function save_main_purchase_data(array $data)
    {
        $ret = Db::connect('db_config_main')->table('purchase_data')->insertGetId($data);
        if (!$ret) {
            Log::write("直充订单号【{$data['order_id']}】写入失败");
            return json(['code' => -1, 'msg' => '直充订单写入失败', 'data' => $data]);
        }
        return $ret;
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

    /*
     * 修改直充订单状态信息
     * @param $order_id       直充订单表编号ID
     * @param $remark   直充订单备注信息
     */
    public function update_purchase_data_status($order_id, $remark)
    {
        $data['order_status'] = 1;
        $data['remark'] = $remark;
        $ret = PurchaseData::where('order_id','=',$order_id)->update($data);
        if (!$ret) {
            Log::write("直充订单编号【{$order_id}】状态修改失败!");
            return false;
        }
        return true;
    }

    /*
     * 根据订单号获取订单信息
     * @param $out_trade_no     直充订单号
     * @param $remark           直充订单备注
     * @return array
     */
    public function get_purchase_data($out_trade_no, $remark)
    {
        $new_data = array();
        $info = PurchaseData::where([['order_id', '=', trim($out_trade_no)], ['order_status', '=', 0]])->find();
        if ($info) {
            $new_data['user_id'] = $info['user_id'];
            $new_data['server_id'] = $info['server_id'];
            $new_data['recharge_id'] = $info['recharge_id'];
            $new_data['pay_type'] = $info['pay_type'];
            $new_data['channel_id'] = $info['channel_id'];
            $new_data['purchase_name'] = $info['purchase_name'];
            $new_data['add_time'] = $info['add_time'];
            $new_data['amount'] = $info['amount'];
            $new_data['remark'] = $remark;
            $new_data['money'] = $info['money'];
            $new_data['pay_ip'] = $info['pay_ip'];
            $new_data['order_id'] = $info['order_id'];
            $new_data['real_server_id'] = $info['real_server_id'];
        }
        return $new_data;
    }

    /*
     * 提交支付
     * @param $pay_type             1、微信支付（汇付宝） 2、支付宝支付
     * @param $money                支付金额
     * @param $out_trade_no         交易订单号
     * @param $payConfigInfo        支付配置信息
     * @return \think\response\Json
     */
    public function submit_pay($pay_type, $money, $out_trade_no, $payConfigInfo)
    {
        if (isset($pay_type) && isset($money) && isset($out_trade_no)) {
            if ($pay_type == 1) {
                if (isset($payConfigInfo)) {
                    /** 微信（官方） **/
                    $this->wx_pay($out_trade_no, $money);
                } else {
                    /** 汇付宝 **/
                    $this->hee_pay($out_trade_no, $money);
                }
            } elseif ($pay_type == 2) {
                /** 支付宝（官方） **/
                $this->zfb_pay($out_trade_no, $money);
            }
        } else {
            return json(['code' => -1, 'msg' => '请求支付参数有误,请核对后再提交!']);
        }
    }

    /*
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
        $params['body'] = "购买异玩平台虚拟道具";
        $params['appid'] = config('pay.wx_pay.app_id');
        $params['mch_id'] = config('pay.wx_pay.merchantId');
        $params['total_fee'] = $total_amount * 100;
        $params['out_trade_no'] = $out_trade_no;
        $params['notify_url'] = config('pay.wx_pay.purchase_notify_url');
        $params['nonce_str'] = $this->genRandomString();
        $params['amount'] = '{"amount":{"total":' . $total_amount . ',"currency":"CNY"}}';
        $ip = $this->get_client_ip();
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
            /* 发起微信支付url */
            $pay_url = $result['mweb_url'] . '&redirect_url=' . urlencode($params['notify_url']);
            /* 数据库操作 */
            self::redirect($pay_url);
        }
    }

    /*
     * 微信支付通知回调
     */
    public function wx_notify()
    {
        $xmlData = file_get_contents('php://input');

        $wxUtil = new WxPayUtil();
        $result = $wxUtil->xml_to_data($xmlData);

        if ($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') {
            $out_trade_no = $result['out_trade_no'];
            $transaction_id = $result['transaction_id'];
            //启动事务
            Db::startTrans();
            $remark = '微信官方支付交易号【transaction_id】:' . $transaction_id;
            $info = $this->get_purchase_data(trim($out_trade_no), $remark);
            if ($info) {
                /* 直充数据写入到Main库直充数据表 */
                $ret = $this->save_main_purchase_data($info);

                /* 新增或更新用户充值统计数据 */
                $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                /* 更新直充订单状态 */
                $this->update_purchase_data_status($info['order_id'], $remark);

                /* 充值元宝(命令发送服务器) */
                test::webw_packet_purchase($info['server_id'], $ret);
            }
            /* 提交事务 */
            Db::commit();
            return View::fetch('purchase/success');
        }
    }

    /*
     * 支付宝支付跳转
     * @param $out_trade_no     平台订单交易号
     * @param $total_amount     订单支付金额
     * @throws \Exception
     */
    public function zfb_pay($out_trade_no, $total_amount)
    {
        if (!empty($out_trade_no) && trim($out_trade_no) != "") {
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $out_trade_no;
            //订单名称，必填
            $subject = "平台虚拟道具购买";
            //付款金额，必填
            $total_amount = $total_amount;
            //商品描述，可空
            $body = $total_amount . "道具礼包";
            //超时时间
            $timeout_express = "1m";

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setTimeExpress($timeout_express);

            $payResponse = new \AlipayTradeService(config('pay.ali_pay'));
            $result = $payResponse->wapPay($payRequestBuilder, config('pay.ali_pay.purchase_return_url'), config('pay.ali_pay.purchase_notify_url'));
            Log::write("支付宝支付返回结果:" . $result);
            return;
        }
    }

    /*
     * 支付通知回调
    */
    /**
     * @throws \Exception
     */
    public function zfb_notify()
    {
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService(config('pay.ali_pay'));
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        $alipaySevice->writeLog($result);
        if ($result) {
            /* 商户订单号 */
            $out_trade_no = $arr['out_trade_no'];
            /* 支付宝交易号 */
            $trade_no = $arr['trade_no'];
            /* 交易状态 */
            $trade_status = $arr['trade_status'];

            if ($trade_status == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($trade_status == 'TRADE_SUCCESS') {
                Db::startTrans();
                try {
                    $remark = '支付宝交易号【trade_no】:' . $trade_no;
                    $info = $this->get_purchase_data(trim($out_trade_no), $remark);
                    if ($info) {
                        /* 直充数据写入到Main库直充数据表 */
                        $ret = $this->save_main_purchase_data($info);

                        /* 新增或更新用户充值统计数据 */
                        $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                        /* 更新直充订单状态 */
                        $this->update_purchase_data_status($info['order_id'], $remark);

                        //直购（直充）道具(命令发送服务器)
                        test::webw_packet_purchase($info['server_id'], $ret);
                    }
                    Db::commit();
                } catch (Exception $exception) {
                    Log::write('直充支付宝支付通知回调事务回滚,exception:' . $exception);
                    /* 特殊处理 start */
                    $this->special_handle_order($out_trade_no, $trade_no);
                    /* 特殊处理 end */
                    Db::rollback();
                }
            }
            echo "success";
        } else {
            echo "fail";
        }
    }

    /**
     * 支付宝支付同步通知回调
     */
    public function zfb_return_url()
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

            return View::fetch('purchase/success');

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            echo "验证失败";
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
            $remark = '交易号【trade_no】:' . $trade_no;
            $info = $this->get_purchase_data($order_id, $remark);
            if ($info) {
                /* 直充数据写入到Main库直充数据表 */
                $ret = $this->save_main_purchase_data($info);

                /* 新增或更新用户充值统计数据 */
                $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                /* 更新直充订单状态 */
                $this->update_purchase_data_status($info['id'], $remark);

                /* 充值元宝(命令发送服务器) */
                test::webw_packet_purchase($info['server_id'], $ret);
            }
        });
    }

    /*
     * 汇付宝支付跳转
     * @param $out_trade_no     平台订单交易号
     * @param $total_amount     订单支付金额
     */
    public function hee_pay($out_trade_no, $total_amount)
    {
        $pay_url = config('pay.hee_pay.pay_url');
        $notify_url = config('pay.hee_pay.purchase_notify_url');
        $return_url = config('pay.hee_pay.purchase_return_url');

        $version = 1;
        $scene = 'h5';
        $pay_type = 30;
        $agent_id = config('pay.hee_pay.agent_id');
        $agent_bill_id = $out_trade_no;
        $agent_bill_time = date('YmdHis');
        $pay_amt = $total_amount; //订单金额
        $ip = request()->ip();
        $user_ip = str_replace('.', '_', $ip);
        $goods_name = urlencode(iconv('UTF-8', 'GBK', '平台直购道具礼包'));
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
        if (array_key_exists('ret_code', $data)) {
            if ($data['ret_code'] == "0000") {
                self::redirect($data['redirectUrl']);
            } else {
                return json(['code' => $data['ret_code'], 'msg' => $data['ret_msg']]);
            }
        }
    }

    /*
     * 汇付宝支付通知回调
    */
    public function hee_notify()
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
            //比较签名密钥结果是否一致，一致则保证了数据的一致性
            echo 'ok';
            //启动事务
            Db::startTrans();
            try {
                $remark = '汇付宝交易号【jnet_bill_no】:' . $jnet_bill_no;
                $info = $this->get_purchase_data(trim($agent_bill_id), $remark);
                if ($info) {
                    /* 直充数据写入到Main库直充数据表 */
                    $ret = $this->save_main_purchase_data($info);

                    /* 新增或更新用户充值统计数据 */
                    $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                    /* 更新直充订单状态 */
                    $this->update_purchase_data_status($info['order_id'], $remark);

                    //充值元宝(命令发送服务器)
                    test::webw_packet_purchase($info['server_id'], $ret);
                }

                /* 提交事务 */
                Db::commit();
            } catch (Exception $exception) {
                Log::write('直充汇付宝支付通知回调事务回滚,exception:' . $exception);
                Db::rollback();
            }
        } else {
            Log::write("汇付宝支付完毕返回ERROR");
            echo 'error';
            //商户自行处理，可通过查询接口更新订单状态，也可以通过商户后台自行补发通知，或者反馈运营人工补发
        }
    }

    /*
     * 汇付宝同步通知回调
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
        $signStr .= '&key=' . config('pay.hee_pay.sign_key'); /* 商户签名密钥  */

        $sign = md5($signStr);
        if ($sign == $return_sign) {
            return redirect('/pay.php/purchase/success');
        }
    }

    /*
     * 通过recharge_id与type获取充值信息
     * @param $recharge_id  充值ID
     * @return array
     */
    public function get_recharge_by_csv($recharge_id)
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

    /*
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

    /*
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param int $len 产生字符串的长度
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

    /*
     * 地址重定向
     * @param $url
     */
    static function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    /**
     * 支付成功跳转页面
     */
    public function success()
    {
        return View::fetch();
    }
}
