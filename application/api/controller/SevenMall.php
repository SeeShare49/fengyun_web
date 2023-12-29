<?php

namespace app\api\controller;

/*
 * 小七商城API
 */

use app\common\HttpSend;
use think\facade\Log;
use think\facade\Request;

class SevenMall
{
    const URL = "https://pay.x7sy.com/x7mall_helper/gateway"; //测试
//    const URL = "https://pay.x7sy.com/x7mall/gateway";      //正式
    const AndroidAppKey = "749dc322bbac9cbdf29fc65761b30d44";
    const IosAppKey = "499e2f1debbfd9d504522beb53a58f74";

    const RsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAh2vJzu3SwCrPFvYXsXFyQa1SotZR0DgIp5LTewCVqRjQKjNiMgyfY0sx6E//9HhbE9h5rdipjSbcxBD0tCbNdj2DTpFW0luJU/Lk8U0+uZm8gKVo9AWrSm4EiPx5ErMCj27sJuLK9pQ8sdMe4v/AUpTFip6gDL+1oqJGrSWPtnHTVNVFoa+AICgZF5NuVjDdPoufXN7+Fox3Z1I/gZD4qdm067BAJWW4/2as7TLA6OcGuSrXhAzz9D0ROV66dDFT3zvyXosXQRNC4SHetO0TpPWlO+lTa495v46N1jdpIPA5dp/FkTJsSAHizHwHnvKdlMjwRljRCqY8QtDJreGRIQIDAQAB";
    const RsaPrivateKey = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCHa8nO7dLAKs8W9hexcXJBrVKi1lHQOAinktN7AJWpGNAqM2IyDJ9jSzHoT//0eFsT2Hmt2KmNJtzEEPS0Js12PYNOkVbSW4lT8uTxTT65mbyApWj0BatKbgSI/HkSswKPbuwm4sr2lDyx0x7i/8BSlMWKnqAMv7WiokatJY+2cdNU1UWhr4AgKBkXk25WMN0+i59c3v4WjHdnUj+BkPip2bTrsEAlZbj/ZqztMsDo5wa5KteEDPP0PRE5Xrp0MVPfO/JeixdBE0LhId607ROk9aU76VNrj3m/jo3WN2kg8Dl2n8WRMmxIAeLMfAee8p2UyPBGWNEKpjxC0Mmt4ZEhAgMBAAECggEATTqkcZVhrmP7jE22P2TCU0BEM0dkiwmZre9nGevAbhoPhTGem4plT5wvjxwojUQiNNQsuH8qWqxCFLLSyvaiD/+OpbzvNbIsRCruc8qorfJF9Vvf2eJtyFmrzm+loItkN2Z72MVYGH17i7IqGCzggngDnEVQY1TlgiVnhxE3v0Sa2HgzcjgnNU8ni6eCK9fpSqx5q+rUkKSn/OtTojnIhIo5Q+4tPtV9+wKcnraKLemezwRPn+/7kI2eM48ZEGr7nEQw9yCST3dtHaquENxND1HS3KKuBq44xqS6OX+C00L90qbbA6mjr14+Tl2bAFWtRtfZwbpQDC7CBdZ3qJrq7QKBgQDqH7rxbpggsXgRFWgE7HGSztXqXOjeaVrK58/TudQmOmDmZPfuCCgdOl0QpjcVP3YMbxVe6i+idNV3A1W5DZIy/C+vtL4mC8TmmMZ8N9OP2+Xoqi4JXdITe7XvU5qKVEsNKaV3OxhW+V6SgqOXP+OfuuVeYyDZZ9Lsz6e4i92A6wKBgQCUExMAIf9pCf9c+JDzLnUYmGzAKy9nHRJ9lrojTUlHksanEnQefU6B6M0zTnYucW8xH0KOlFGx2k2adpp1B9L/4KGh9wunOf4yYjVYLS1dfFBKuHm6as8i9LeiBb+MDxtt1tG7WzNF90lqi5ZqA/EfpJsXWln6BCo8ZbYkhDaTIwKBgDlO7wmuEpreFg/Id5/M6LgQ3RfzCiV3EHxeZebjs+RvocyDppSjdn5BDQrIRO90i0bb4hVLqcFQa/gn3tHfWowUYu/VhD3334i6mMtLNwQdxZdAIOi2CHgwbgZZJpj5rS1ZpmEbHAgeHmIqjDS3tNYif4atKRtOaLkC04E+60dlAoGACAjQ6xztIuHr/TtvggJvUBpVLEgrlki5jB71kpzK8RtBtCcILe9Wpy3elH4ZY4O8KMotblcV57SUqWDuGJOIG2Iw4vzooPZAmLNDeblZ0MzB/ovr7vWEr7zfiecN8aeQMXGKh8P3EEDOtv1D+BSmrLTdxXKdlzdKo2RRccHKxEkCgYBx1PRHaDa6lu7z+mPRdmBgVH0QkN2Dzz46DenddfaSINOhEU2vPvyxT3hyuD2rF8vDflD7wybRJfre1BjZ6H2QTxXH18T+2X5Gq4MOd2P1E591ydhDjNXNc5NgHcoEaIy1Mif8748rtc84Scgy672mbqK83PFJ6nBGshk0NEgUHg==";


    //RSA密钥对，可以从签名助手页面获取测试秘钥对
    const TestRsaPrivateKey = "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDDxRpuKIdYQ3lRHlIHJLbwJUKJVSVORZEISw/flJmz1rXATtuiKuPSGmDvDK7qF7J0imX3gF7DPAzckd7jlL9Ri95hCj/Ovclvjq+mgkxA+KQ3JlI5HGaM+WjMi4CVhbnpfcB6t75iQfW4IV556pbQFxPg2+WOfe/IkZiXM7eRYK6KdkfTCGvFK2LJvlImmOWDHP4xVS4EAkINgBGjpYK4IBNO537/3vCykEJ5yz+W29RA9oal6EKFsLbhTdUGYuxIKbGMA8tAki6QfqzLq1WgrmOaM2cWwKtZuR3rwfq2yb10Y9xQyA1bF5qFT/y1I61f3gBpN9Thynzjpift0fglAgMBAAECggEAIVoJ96xl6m6MU3qD5P2nQOBIJpdf5KbLX4tSJ/fr+4xfqGSG3GjMKTYfP3p8rhrdZydQ2cp/2mj3k/gx7bmgombeus+BMVp538yCNi7KiOMTLuYTafFhszCmXvqBLHf8xT+MNBvrjlfIYdclfkWt7cOQumUcBZuE5zmOsmu4IUb4ArjKsEmGDqdsp3fCESzbMWqBinvOmdUy+3VbBTpSjU/PDWCd/OgnTHpwPY7O9T7zSo8grkkWyq79W5kz7PLXdtqt1DNChUjHHv+ANM3T5L127BlHDskyG24AGrDzLmCmNQab4qFtOjPtHdsXsQm8cllgzD4lvP6ThuAG8DK4IQKBgQDnoCY8CLc2kUNyibdaA5N7y4xaY9vm1t6jKf37fOS069GkUNfOb8r+Pxp70UAKzMBWZ4iVlDbEh3FU0jveYutsUUbjEB4qI19/jvUHahKzaE9DCK3HkGvjT3NkQmZ+LslS/CxJtyZl+mN8/Ur6OA7p/l8BuJMVMXY+G7mQRZVtzQKBgQDYXwZX712DP5DTLcoUwOM9mIa8UFIFoZN+lwKLq8c0wbpZ3uicU90YwCPVvuATp/fwjOgOEId8HkLeBXkbqkJRimI0y4bTPzHNk0JDlTso79ERcglA9wU4j3C6OArB0Id/u2cVSex/JW3arQt2jRk5JCSDtWn7k1cB9qPvckUbuQKBgA3nYSQtacIOyjuv5J+0oz/FIjGy2Npsf4TP2n0kLB5oIXd5mtq7fzXv18ki8HM1gz4sjNhdw0Pc1YK/8/QPgA5KerTanNTutqbTkAXX6jN2yXs+pB/cnX1RoZ2dFsXwTQl8NbRfGCD6/Mnd8og+oTaOnGlgCQQ2qeBkjakJZETpAoGBAIu/FAHHf8Y9T/SVJmexDRPDZ4JI/jDU4sZoEiTTlZ3lYc6ZwfL1118c+ggbd+46FlEvMNGkq1zmzplHP6k2lg7EKhmfOj1GG4yDB9FOmR8fhRCXbpKe+KhHPK+JcqkrXdiJ2VJOpIiaTBFoona3OwtE5LCMgx8RUqjZ+5ezXh9BAoGBAIz5//GXs1kJoGQatr0rvE4CvVLVbKHaVaXy7MAcp4uya3g1cZh3Swje8e4RD6UfiKmx+aPETfw9IkXbrPTYmXCNDx5bSbbPL+WMY8c7F7K0krOfl8Vtzg5cCfo45+IsLCM16sDT0MhLCPUMWj0kcd4bKcAc3CENaL3U03oLE58r";       //小七平台测试私钥
    const TestRsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw8UabiiHWEN5UR5SByS28CVCiVUlTkWRCEsP35SZs9a1wE7boirj0hpg7wyu6heydIpl94BewzwM3JHe45S/UYveYQo/zr3Jb46vpoJMQPikNyZSORxmjPlozIuAlYW56X3Aere+YkH1uCFeeeqW0BcT4Nvljn3vyJGYlzO3kWCuinZH0whrxStiyb5SJpjlgxz+MVUuBAJCDYARo6WCuCATTud+/97wspBCecs/ltvUQPaGpehChbC24U3VBmLsSCmxjAPLQJIukH6sy6tVoK5jmjNnFsCrWbkd68H6tsm9dGPcUMgNWxeahU/8tSOtX94AaTfU4cp846Yn7dH4JQIDAQAB";        //小七平台测试公钥
    const HttpMethod = "POST";
    const GameType = "client";


    public function getparam()
    {
        $param = Request::post();
        Log::write("param:".json_encode($param,true));
    }

    /**
     * 道具信息查询
     **/
    public function getPropInfo()
    {
        if (Request::isPost()) {
            $param = Request::param();
            Log::write('道具信息查询参数:');
            if (isset($param)) {
                $apiMethod = "x7mall.propQuery";
//                if (array_key_exists('osType', $param)) {
//                    $osType = $param['osType'];//系统类型，ios或android
//                    if (empty($osType)) {
//                        $osType = "android";
//                    }
//                } else {
//                    $osType = "android";
//                }
//                $appkey = $osType == 'ios' ? self::IosAppKey : self::AndroidAppKey;
                $appkey =  self::AndroidAppKey;
                $reqTime = date(DATE_ISO8601, time());
                $bizParams = $param['bizParams'];
                Log::write("道具信息查询 bizParams参数:" . $bizParams);
                //生成payload
                $payload = self::genPayload($apiMethod, $appkey, $reqTime, $bizParams, self::GameType);
                Log::write("生成payload:".$payload);
                //生成签名
                $signature = self::sign($payload, self::RsaPrivateKey);
//                $signature = self::sign($payload, self::RsaPrivateKey);
                Log::write("生成签名字符串:".$signature);
                //签名校验
                $passed = self::verify($payload, $signature, self::RsaPublicKey);
//                $passed = self::verify($payload, $signature, self::RsaPublicKey);
                Log::write("道具信息查询签名校验:". $passed);
                if ($passed) {
                    $data['bizParams'] = $bizParams;
                    $data['apiMethod'] = $apiMethod;
                    $data['reqTime'] = $reqTime;
                    $data['appkey'] = $appkey;
                    $data['gameType'] = self::GameType;
                    $data['signature'] = $signature;


//                    $data[] = [
//                        "bizParams"=>$bizParams,
//                        "apiMethod"=>$apiMethod,
//                        "reqTime"=>$reqTime,
//                        "appkey"=>$appkey,
//                        "gameType"=>self::GameType,
//                        "signature"=>$signature
//                    ];


                    $post_data = "bizParams=".$bizParams."&apiMethod=".$apiMethod."&reqTime=".$reqTime."&appkey=".$appkey."&gameType=".self::GameType."&signature=".$signature."";

                    Log::write("请求地址:".self::URL);

                    Log::write("请求参数:".$post_data);


                    $result = HttpSend::curPost(self::URL,$data);
                    $result = json_decode($result, true);
                    Log::write("get prop info result:".$result);
                    Log::write("bizResp:" . $result['bizResp']);
                    $bizResp = json_decode($result['bizResp'], true);
                    if ($bizResp['respCode'] == 'SUCCESS') {
                        return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg'], 'props' => $bizResp['props']]);
                    }
                    return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg']]);

                } else {
                    return json(['respCode' => -1, 'respMsg' => '签名校验失败!']);
                }
            }
        }
        return json(['respCode' => -1, 'respMsg' => '道具信息查询,请求方法错误!']);
    }


    /**
     * 角色信息查询
     */
    public function getRoleInfo()
    {
        if (Request::isPost()) {
            $param = Request::param();
            if (isset($param)) {
                $apiMethod = "x7mall.roleQuery";
//                if (array_key_exists('osType', $param)) {
//                    $osType = $param['osType'];//系统类型，ios或android
//                    if (empty($osType)) {
//                        $osType = "android";
//                    }
//                } else {
//                    $osType = "android";
//                }

                /** @var TYPE_NAME $appkey */
                $appkey =self::AndroidAppKey;// $osType == 'ios' ? self::IosAppKey : self::AndroidAppKey;
                $reqTime = date(DATE_ISO8601, time());
                $bizParams = $param['bizParams'];
                //生成payload
                $payload = self::genPayload($apiMethod, $appkey, $reqTime, $bizParams, self::GameType, self::HttpMethod);
                Log::write("生成payload:" . $payload);
                //生成签名
                $signature = self::sign($payload, self::RsaPrivateKey);
                Log::write("生成签名 signature:" . $signature);
                //签名校验
                $passed = self::verify($payload, $signature, self::RsaPublicKey);
                if ($passed) {
//                    $result = HttpSend::send_request(self::URL, $bizParams);
                    Log::write("角色信息查询bizParams:" . $bizParams);
                    Log::write("角色信息查询json encode bizParams" . json_encode($bizParams));

                    $data['bizParams'] = $bizParams;
                    $data['apiMethod'] = $apiMethod;
                    $data['reqTime'] = $reqTime;
                    $data['appkey'] = $appkey;
                    $data['gameType'] = self::GameType;
                    $data['signature'] = $signature;

                    Log::write("data未进行json_encode:".is_array($data));
                    Log::write("请求地址:".self::URL);

                    Log::write("请求参数:".json_encode($data));
                    $result = HttpSend::curPost(self::URL, json_encode($data));

                    Log::write("get role info result:" . $result);
                    $result = json_decode($result, true);
                    $bizResp = json_decode($result['bizResp'], true);
                    Log::write("resp code:" . $bizResp['respCode']);
                    if ($bizResp['respCode'] == 'SUCCESS') {
                        return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg'], 'role' => $bizResp['role'], 'guidRoles' => $bizResp['guidRoles']]);
                    }
                    return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg']]);
                } else {
                    return json(['respCode' => -1, 'respMsg' => '签名校验失败!']);
                }
            }
        }
        return json(['respCode' => -1, 'respMsg' => '角色信息查询,请求方法错误!']);
    }

    /**
     * 道具发放
     **/
    public function distributeProp()
    {
        if (Request::isPost()) {
            $param = Request::param();
            if (isset($param)) {
                $apiMethod = "x7mall.propIssue";
//                if (array_key_exists('osType', $param)) {
//                    $osType = $param['osType'];//系统类型，ios或android
//                    if (empty($osType)) {
//                        $osType = "android";
//                    }
//                } else {
//                    $osType = "android";
//                }
                $appkey =self::AndroidAppKey;// $osType == 'ios' ? self::IosAppKey : self::AndroidAppKey;
                $reqTime = date(DATE_ISO8601, time());
                $bizParams = $param['bizParams'];
                //生成payload
                $payload = self::genPayload($apiMethod, $appkey, $reqTime, $bizParams, self::GameType);
                //生成签名
                $signature = self::sign($payload, self::RsaPrivateKey);
                //签名校验
                $passed = self::verify($payload, $signature, self::RsaPublicKey);
                if ($passed) {
                    $data[] = array();
                    $data['bizParams'] = $bizParams;
                    $data['apiMethod'] = $apiMethod;
                    $data['reqTime'] = $reqTime;
                    $data['appkey'] = $appkey;
                    $data['gameType'] = self::GameType;
                    $data['signature'] = $signature;

                    $result = HttpSend::curPost(self::URL, $data);
                    $result = json_decode($result, true);
                    Log::write("bizResp:" . $result['bizResp']);
                    $bizResp = json_decode($result['bizResp'], true);
                    return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg']]);
                } else {
                    return json(['respCode' => -1, 'respMsg' => '签名校验失败!']);
                }
            }
        }
        return json(['respCode' => -1, 'respMsg' => '道具发放,请求方法错误!']);
    }

    /**
     * 商城入口查询
     **/
    public function mallEntry()
    {
        if (Request::isPost()) {
            $param = Request::param();
            Log::write('商城入口查询参数:');
            if (isset($param)) {
                $apiMethod = "x7mall.mallEntry";
//                if (array_key_exists('osType', $param)) {
//                    $osType = $param['osType'];//系统类型，ios或android
//                    if (empty($osType)) {
//                        $osType = "android";
//                    }
//                } else {
//                    $osType = "android";
//                }
                $appkey =self::AndroidAppKey;// $osType == 'ios' ? self::IosAppKey : self::AndroidAppKey;
                $reqTime = date(DATE_ISO8601, time());
                $bizParams = $param['bizParams'];
                //生成payload
                $payload = self::genPayload($apiMethod, $appkey, $reqTime, $bizParams, self::GameType);
                //生成签名
                $signature = self::sign($payload, self::RsaPrivateKey);
                //签名校验
                $passed = self::verify($payload, $signature, self::RsaPublicKey);
                if ($passed) {


                    $data['bizParams'] = $bizParams;
                    $data['apiMethod'] = $apiMethod;
                    $data['reqTime'] = $reqTime;
                    $data['appkey'] = $appkey;
                    $data['gameType'] = self::GameType;
                    $data['signature'] = $signature;

                    $result = HttpSend::curPost(self::URL, $data);
                    $result = json_decode($result, true);
                    Log::write("bizResp:" . $result['bizResp']);
                    $bizResp = json_decode($result['bizResp'], true);
                    if ($bizResp['respCode'] == 'SUCCESS') {
                        return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg'], 'props' => $bizResp['props']]);
                    }
                    return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg']]);
                } else {
                    return json(['respCode' => -1, 'respMsg' => '签名校验失败!']);
                }
            }
        }
        return json(['respCode' => -1, 'respMsg' => '商城入口查询,请求方法错误!']);
    }

    /**
     * 订单通告
     **/
    public function orderNotify()
    {
        if (Request::isPost()) {
            $param = Request::param();
            Log::write('订单通告参数:');
            error_log(print_r($param, 1));
            if (isset($param)) {
                $apiMethod = "x7mall.orderNotify";
//                if (array_key_exists('osType', $param)) {
//                    $osType = $param['osType'];//系统类型，ios或android
//                    if (empty($osType)) {
//                        $osType = "android";
//                    }
//                } else {
//                    $osType = "android";
//                }
                $appkey =self::AndroidAppKey;// $osType == 'ios' ? self::IosAppKey : self::AndroidAppKey;
                $reqTime = date(DATE_ISO8601, time());
                $bizParams = $param['bizParams'];
                //生成payload
                $payload = self::genPayload($apiMethod, $appkey, $reqTime, $bizParams, self::GameType);
                //生成签名
                $signature = self::sign($payload, self::RsaPrivateKey);
                //签名校验
                $passed = self::verify($payload, $signature, self::RsaPublicKey);
                if ($passed) {
                    $data['bizParams'] = $bizParams;
                    $data['apiMethod'] = $apiMethod;
                    $data['reqTime'] = $reqTime;
                    $data['appkey'] = $appkey;
                    $data['gameType'] = self::GameType;
                    $data['signature'] = $signature;

                    $result = HttpSend::curPost(self::URL, $data);
                    $result = json_decode($result, true);
                    Log::write("bizResp:" . $result['bizResp']);
                    $bizResp = json_decode($result['bizResp'], true);
                    return json(['respCode' => $bizResp['respCode'], 'respMsg' => $bizResp['respMsg']]);
                } else {
                    return json(['respCode' => -1, 'respMsg' => '签名校验失败!']);
                }
            }
        }
        return json(['respCode' => -1, 'respMsg' => '订单通告,请求方法错误!']);
    }


    /**
     * 签名
     *
     * @param string $payload
     * @param string $rsaPrivateKey
     * @param int $algo
     * @return string
     */
    public static function sign($payload, $rsaPrivateKey, $algo = OPENSSL_ALGO_SHA256)
    {
        $formatPrivateKey = self::formatRsaPrivateKey($rsaPrivateKey);
        openssl_sign($payload, $signature, $formatPrivateKey, $algo);
        return base64_encode($signature);
    }

    /**
     * 验签
     *
     * @param string $payload
     * @param string $signature
     * @param string $rsaPublicKey
     * @param int $algo
     * @return int
     */
    public static function verify($payload, $signature, $rsaPublicKey, $algo = OPENSSL_ALGO_SHA256)
    {
        $rawSignature = base64_decode($signature);
        $formatPublicKey = self::formatRsaPublicKey($rsaPublicKey);
        return openssl_verify($payload, $rawSignature, $formatPublicKey, $algo);
    }

    /**
     * 格式化公钥
     *
     * @param string $publicKey
     * @return string
     */
    public static function formatRsaPublicKey($publicKey)
    {
        return "-----BEGIN PUBLIC KEY-----\r\n" . wordwrap($publicKey, 64, "\r\n", TRUE) . "\r\n-----END PUBLIC KEY-----";
    }

    /**
     * 格式化私钥
     *
     * @param string $privateKey
     * @return string
     */
    public static function formatRsaPrivateKey($privateKey)
    {
        return "-----BEGIN RSA PRIVATE KEY-----\r\n" . wordwrap($privateKey, 64, "\r\n", TRUE) . "\r\n-----END RSA PRIVATE KEY-----";
    }

    /**
     * 生成payload
     *
     * @param string $apiMethod
     * @param string $appkey
     * @param string $datetime
     * @param string $body
     * @param string $gameType
     * @param string $method
     * @return string
     */
    public static function genPayload($apiMethod, $appkey, $datetime, $body, $gameType, $method = "POST")
    {
        $payload = $method . " " . $apiMethod . "@" . $appkey . "#" . $gameType . "."
            . $datetime . "\n\n" . $body;
        Log::write("gen pay load body:" . $body);
        Log::write("gen pay load str:" . $payload);
        return $payload;
    }
}
