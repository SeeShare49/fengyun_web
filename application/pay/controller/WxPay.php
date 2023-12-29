<?php

namespace app\pay\controller;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use think\facade\Config;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use think\Controller;

class WxPay extends Controller
{

    public function index()
    {
        echo '微信支付首页......';
        echo PHP_EOL;
        echo Config::get('pay.wx_pay.merchantId');
    }


    /**
     * 微信H5支付统一下单
     */
    public function wx_h5_create_order()
    {
        // 商户相关配置
        $merchantId = Config::get('pay.wx_pay.merchantId'); // 商户号
        $merchantSerialNumber = Config::get('pay.wx_pay.merchantSerialNumber'); // 商户API证书序列号
        $merchantPrivateKey = PemUtil::loadPrivateKey(dirname(__DIR__).'/cert/apiclient_key.pem'); // 商户aip证书私钥
        // 微信支付平台配置
        $wechatpayCertificate = PemUtil::loadCertificate(dirname(__DIR__).'/cert/apiclient_cert.pem'); // 微信支付平台证书


        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
            ->withWechatPay([$wechatpayCertificate]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = GuzzleHttp\HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $client = new GuzzleHttp\Client(['handler' => $stack]);


        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $resp = $client->request(
                'POST',
                'https://api.mch.weixin.qq.com/v3/pay/transactions/h5', //请求URL
                [
                    // JSON请求体
                    'json' => [
                        "time_expire" => "2018-06-08T10=>34=>56+08=>00",
                        "amount" => [
                            "total" => 100,
                            "currency" => "CNY"
                        ],
                        "mchid" => "1230000109",
                        "description" => "Image形象店-深圳腾大-QQ公仔",
                        "notify_url" => " https=>//www.weixin.qq.com/wxpay/pay.php",
                        "out_trade_no" => "1217752501201407033233368018",
                        "goods_tag" => "WXG",
                        "appid" => "wxd678efh567hg6787",
                        "attach" => "自定义数据说明",
                        "detail" => [
                            "invoice_id" => "wx123",
                            "goods_detail" => [[
                                "goods_name" => "iPhoneX 256G",
                                "wechatpay_goods_id" => "1001",
                                "quantity" => 1,
                                "merchant_goods_id" => "商品编码",
                                "unit_price" => 828800
                            ], [
                                "goods_name" => "iPhoneX 256G",
                                "wechatpay_goods_id" => "1001",
                                "quantity" => 1,
                                "merchant_goods_id" => "商品编码",
                                "unit_price" => 828800
                            ]],
                            "cost_price" => 608800
                        ],
                        "scene_info" => [
                            "device_id" => "013467007045764",
                            "store_info" => [
                                "address" => "广东省深圳市南山区科技中一道10000号",
                                "area_code" => "440305",
                                "name" => "腾讯大厦分店",
                                "id" => "0001"
                            ],
                            "h5_info" => [
                                "app_name" => "王者荣耀",
                                "app_url" => "https=>//pay.qq.com",
                                "bundle_id" => "com.tencent.wzryiOS",
                                "package_name" => "com.tencent.tmgp.sgame",
                                "type" => "iOSAndroidWap"
                            ],
                            "payer_client_ip" => "14.23.150.211"
                        ]
                    ]
                ]
            );
            $statusCode = $resp->getStatusCode();
            if ($statusCode == 200) { //处理成功
                echo "success,return body = " . $resp->getReasonPhrase() . "\n";
            } else if ($statusCode == 204) { //处理成功，无返回Body
                echo "success";
            }
        } catch (RequestException $e) {
            // 进行错误处理
            echo $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "failed,resp code = " . $e->getResponse()->getStatusCode() . " return body = " . $e->getResponse()->getBody() . "\n";
            }
            return;

        }
    }

    /**
     * 微信APP支付统一下单
     */
    public function wx_app_create_order()
    {
        // 商户相关配置
        $merchantId = Config::get('pay.wx_pay.merchantId'); // 商户号
        $merchantSerialNumber = Config::get('pay.wx_pay.merchantSerialNumber'); // 商户API证书序列号
        $merchantPrivateKey = PemUtil::loadPrivateKey('./cert/apiclient_key.pem'); // 商户aip证书私钥
        // 微信支付平台配置
        $wechatpayCertificate = PemUtil::loadCertificate('./cert/wechatPay_cert.pem'); // 微信支付平台证书


        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
            ->withWechatPay([$wechatpayCertificate]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = GuzzleHttp\HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $client = new GuzzleHttp\Client(['handler' => $stack]);


        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $resp = $client->request(
                'POST',
                'https://api.mch.weixin.qq.com/v3/pay/transactions/app', //请求URL
                [
                    // JSON请求体
                    'json' => [
                        "time_expire" => "2018-06-08T10=>34=>56+08=>00",
                        "amount" => [
                            "total" => 100,
                            "currency" => "CNY"
                        ],
                        "mchid" => "1230000109",
                        "description" => "Image形象店-深圳腾大-QQ公仔",
                        "notify_url" => " https=>//www.weixin.qq.com/wxpay/pay.php",
                        "out_trade_no" => "1217752501201407033233368018",
                        "goods_tag" => "WXG",
                        "appid" => "wxd678efh567hg6787",
                        "attach" => "自定义数据说明",
                        "detail" => [
                            "invoice_id" => "wx123",
                            "goods_detail" => [[
                                "goods_name" => "iPhoneX 256G",
                                "wechatpay_goods_id" => "1001",
                                "quantity" => 1,
                                "merchant_goods_id" => "商品编码",
                                "unit_price" => 828800
                            ], [
                                "goods_name" => "iPhoneX 256G",
                                "wechatpay_goods_id" => "1001",
                                "quantity" => 1,
                                "merchant_goods_id" => "商品编码",
                                "unit_price" => 828800
                            ]],
                            "cost_price" => 608800
                        ],
                        "scene_info" => [
                            "store_info" => [
                                "address" => "广东省深圳市南山区科技中一道10000号",
                                "area_code" => "440305",
                                "name" => "腾讯大厦分店",
                                "id" => "0001"
                            ],
                            "device_id" => "013467007045764",
                            "payer_client_ip" => "14.23.150.211"
                        ]
                    ]
                ]
            );
            $statusCode = $resp->getStatusCode();
            if ($statusCode == 200) { //处理成功
                echo "success,return body = " . $resp->getReasonPhrase() . "\n";
            } else if ($statusCode == 204) { //处理成功，无返回Body
                echo "success";
            }
        } catch (RequestException $e) {
            // 进行错误处理
            echo $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "failed,resp code = " . $e->getResponse()->getStatusCode() . " return body = " . $e->getResponse()->getBody() . "\n";
            }
            return;

        }
    }

    /**
     * 公众号支付统一下单
     */
    public function wx_js_api_create_order()
    {
        // 商户相关配置
        $merchantId = Config::get('pay.wx_pay.merchantId'); // 商户号
        $merchantSerialNumber = Config::get('pay.wx_pay.merchantSerialNumber'); // 商户API证书序列号
        $merchantPrivateKey = PemUtil::loadPrivateKey('./cert/apiclient_key.pem'); // 商户aip证书私钥
        // 微信支付平台配置
        $wechatpayCertificate = PemUtil::loadCertificate('./cert/wechatPay_cert.pem'); // 微信支付平台证书


        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
            ->withWechatPay([$wechatpayCertificate]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = GuzzleHttp\HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $client = new GuzzleHttp\Client(['handler' => $stack]);


        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $resp = $client->request(
                'POST',
                'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi', //请求URL
                [
                    // JSON请求体
                    'json' => [
                        [
                            "time_expire" => "2018-06-08T10:34:56+08:00",
                            "amount" => [
                                "total" => 100,
                                "currency" => "CNY"
                            ],
                            "mchid" => "1230000109",
                            "description" => "Image形象店-深圳腾大-QQ公仔",
                            "notify_url" => " https://www.weixin.qq.com/wxpay/pay.php",
                            "payer" => [
                                "openid" => "oUpF8uMuAJO_M2pxb1Q9zNjWeS6o"
                            ],
                            "out_trade_no" => "1217752501201407033233368018",
                            "goods_tag" => "WXG",
                            "appid" => "wxd678efh567hg6787",
                            "attach" => "自定义数据说明",
                            "detail" => [
                                "invoice_id" => "wx123",
                                "goods_detail" => [[
                                    "goods_name" => "iPhoneX 256G",
                                    "wechatpay_goods_id" => "1001",
                                    "quantity" => 1,
                                    "merchant_goods_id" => "商品编码",
                                    "unit_price" => 828800
                                ], [
                                    "goods_name" => "iPhoneX 256G",
                                    "wechatpay_goods_id" => "1001",
                                    "quantity" => 1,
                                    "merchant_goods_id" => "商品编码",
                                    "unit_price" => 828800
                                ]],
                                "cost_price" => 608800
                            ],
                            "scene_info" => [
                                "store_info" => [
                                    "address" => "广东省深圳市南山区科技中一道10000号",
                                    "area_code" => "440305",
                                    "name" => "腾讯大厦分店",
                                    "id" => "0001"
                                ],
                                "device_id" => "013467007045764",
                                "payer_client_ip" => "14.23.150.211"
                            ]
                        ]
                    ]
                ]
            );
            $statusCode = $resp->getStatusCode();
            if ($statusCode == 200) { //处理成功
                echo "success,return body = " . $resp->getReasonPhrase() . "\n";
            } else if ($statusCode == 204) { //处理成功，无返回Body
                echo "success";
            }
        } catch (RequestException $e) {
            // 进行错误处理
            echo $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "failed,resp code = " . $e->getResponse()->getStatusCode() . " return body = " . $e->getResponse()->getBody() . "\n";
            }
            return;

        }
    }

    /**
     * Native（扫码）支付统一订单
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function native_create_order()
    {
        // 商户相关配置
        $merchantId = Config::get('pay.wx_pay.merchantId'); // 商户号
        $merchantSerialNumber = Config::get('pay.wx_pay.merchantSerialNumber'); // 商户API证书序列号
        $merchantPrivateKey = PemUtil::loadPrivateKey('./cert/apiclient_key.pem'); // 商户aip证书私钥
        // 微信支付平台配置
        $wechatpayCertificate = PemUtil::loadCertificate('./cert/wechatPay_cert.pem'); // 微信支付平台证书


        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
            ->withWechatPay([$wechatpayCertificate]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = GuzzleHttp\HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $client = new GuzzleHttp\Client(['handler' => $stack]);


        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $resp = $client->request(
                'POST',
                'https://api.mch.weixin.qq.com/v3/pay/transactions/native', //请求URL
                [
                    // JSON请求体
                    'json' => [
                        "time_expire" => "2018-06-08T10:34:56+08:00",
                        "amount" => [
                            "total" => 100,
                            "currency" => "CNY",
                        ],
                        "mchid" => "1230000109",
                        "description" => "Image形象店-深圳腾大-QQ公仔",
                        "notify_url" => " https://www.weixin.qq.com/wxpay/pay.php",
                        "out_trade_no" => "1217752501201407033233368018",
                        "goods_tag" => "WXG",
                        "appid" => "wxd678efh567hg6787",
                        "attach" => "自定义数据说明",
                        "detail" => [
                            "invoice_id" => "wx123",
                            "goods_detail" => [
                                [
                                    "goods_name" => "iPhoneX 256G",
                                    "wechatpay_goods_id" => "1001",
                                    "quantity" => 1,
                                    "merchant_goods_id" => "商品编码",
                                    "unit_price" => 828800,
                                ],
                                [
                                    "goods_name" => "iPhoneX 256G",
                                    "wechatpay_goods_id" => "1001",
                                    "quantity" => 1,
                                    "merchant_goods_id" => "商品编码",
                                    "unit_price" => 828800,
                                ],
                            ],
                            "cost_price" => 608800,
                        ],
                        "scene_info" => [
                            "store_info" => [
                                "address" => "广东省深圳市南山区科技中一道10000号",
                                "area_code" => "440305",
                                "name" => "腾讯大厦分店",
                                "id" => "0001",
                            ],
                            "device_id" => "013467007045764",
                            "payer_client_ip" => "14.23.150.211",
                        ]
                    ],
                    'headers' => ['Accept' => 'application/json']
                ]
            );
            $statusCode = $resp->getStatusCode();
            if ($statusCode == 200) { //处理成功
                echo "success,return body = " . $resp->getReasonPhrase() . "\n";
            } else if ($statusCode == 204) { //处理成功，无返回Body
                echo "success";
            }
        } catch (RequestException $e) {
            // 进行错误处理
            echo $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "failed,resp code = " . $e->getResponse()->getStatusCode() . " return body = " . $e->getResponse()->getBody() . "\n";
            }
            return;

        }
    }

    /**
     * 微信支付异步通知回调
     */
    public function notify_url()
    {
        echo 'notify_url';
    }
}
