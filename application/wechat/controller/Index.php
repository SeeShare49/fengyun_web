<?php

namespace app\wechat\controller;

use think\Controller;
use think\facade\Config;
use think\Request;

class Index
{
    public function index(){
        echo '微信支付首页......';
//        echo PHP_EOL;
//        echo Config::get('pay.wx_pay.merchantId');
    }
}
