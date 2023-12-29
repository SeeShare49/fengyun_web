<?php

namespace app\pay\controller;

use think\facade\Log;
use think\facade\View;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/service/AlipayTradeService.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . './config.php';

class ZfbPay
{

    public function index()
    {
        return View::fetch();
    }

    public function pay()
    {
        if (!empty($_POST['WIDout_trade_no']) && trim($_POST['WIDout_trade_no']) != "") {
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $_POST['WIDout_trade_no'];
            //订单名称，必填
            $subject = $_POST['WIDsubject'];
            //付款金额，必填
            $total_amount = $_POST['WIDtotal_amount'];
            //商品描述，可空
            $body = $_POST['WIDbody'];
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
        return View::fetch();
    }

    public function notify_url()
    {
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService(config('pay.ali_pay'));
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        Log::write("支付宝notify_url返回result:");
        $alipaySevice->writeLog($result);
        /**
        'gmt_create' => '2021-03-19 09:55:58',
        'charset' => 'UTF-8',
        'seller_email' => '58455@qq.com',
        'subject' => '测试',
        'sign' => 'MrZGJyNEv2Z5LrIlN36J8MQ/6w9tbU85/DRvQ6A2vvnwcwTKWZTts1FRO/l2xmx504btDfD6k6iDAMO4bPpqollWF0WIG+4AC4iyBRUWj7NeN+IhKMZQlzS6o10wrjid9gn4kpnfah4fL/aArqC9clONyTL7cNLYtqzpFCkj63ECdcPN/4XSIKtsAGcDReBs32/hyvijiISSEGmy4mzp3g0wgmB8/OoLXo/VEXWmbSfIyWYX9mZFXrWc2FxTng5dI0hyBdblCx20Q2NPCwQHSlnfsBGIlrM1RDk+bjWb421Z+Uc4j1+qUgJ9VY4cX4nZW0pGTwpch1Lj9VZVGFGJBw==',
        'body' => '购买测试商品0.01元',
        'buyer_id' => '2088202428051549',
        'invoice_amount' => '0.01',
        'notify_id' => '2021031900222095558051541421958006',
        'fund_bill_list' => '[{"amount":"0.01","fundChannel":"PCREDIT"}]',
        'notify_type' => 'trade_status_sync',
        'trade_status' => 'TRADE_SUCCESS',
        'receipt_amount' => '0.01',
        'buyer_pay_amount' => '0.01',
        'app_id' => '2021002124676575',
        'sign_type' => 'RSA2',
        'seller_id' => '2088931081994271',
        'gmt_payment' => '2021-03-19 09:55:58',
        'notify_time' => '2021-03-19 09:55:59',
        'version' => '1.0',
        'out_trade_no' => '202131995552427',
        'total_amount' => '0.01',
        'trade_no' => '2021031922001451541400565216',
        'auth_app_id' => '2021002124676575',
        'buyer_logon_id' => 'a01***@sina.com',
        'point_amount' => '0.00',
        **/
        if ($result) {
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";
        } else {
            echo "fail";
        }
    }
}
