<?php

namespace app\api\controller;

use think\Controller;
use think\facade\Log;
use think\Request;

/**
 * 百度推广广告接收接口
 **/
class ReceiveBd
{
    const BAIDU_OCPC_URL = 'https://ocpc.baidu.com/ocpcapi/api/uploadConvertData';
    const RETRY_TIMES = 3;
    const A_KEY = 'MzMxNTY3NDk=';//推广账户akey

    public function index()
    {
        Log::write("receive BaiDu index.....");

        $param = \think\facade\Request::param();
        $callbackUrl = $param['callback_url'];

        // 需替换为推广账户akey
        $akey = 'MzMxNTY3NDk=';

        $this->callback_func($callbackUrl, $akey);//回传激活转化
        $this->callback_func_register($callbackUrl, self::A_KEY);//回传注册转化
        $this->callback_func_retain_1day($callbackUrl, self::A_KEY);//回传次日留存转化

    }

    public function feed()
    {
        Log::write("start feed......");
        $param = \think\facade\Request::param();
        $callbackUrl = $param['callback_url'];
        var_dump('call back url:' . $callbackUrl);
        $this->callback_func($callbackUrl, self::A_KEY);
        $this->callback_func_register($callbackUrl, self::A_KEY);
        $this->callback_func_retain_1day($callbackUrl, self::A_KEY);
        Log::write("end feed......");
    }


    public function test()
    {
        $akey = 'MzMxNTY3NDk=';
        $param = \think\facade\Request::param();
        $callback_url = $param['callback_url'];
        // 示例url
        // http://ocpc.baidu.com/ocpcapi/cb/actionCb?a_type={{ATYPE}}&a_value={{AVALUE}}&s=123&ext_info=uANBIyTKHgGPXD4RyHPrwA_q0RdlHg9rN7b1Hbw_ub4JRgI5N7bzyZG7nbdJigI5w7DYH-wuPD4Jm1YKH-NnH07rNRkn0R4NHDs

        $this->callback_func($callback_url, $akey);

    }


    /**
     * callbackFunc 调用callback url 向百度回传app激活转化数据
     * @param 监测地址中callback_url参数值
     * @param 当前账户对应akey
     * @return bool 成功返回true，失败返回false
     */
    function callback_func($callbackurl, $akey)
    {
        Log::write("call back url:" . $callbackurl);
        $url = urldecode($callbackurl);

        /**
         * 注意：示例为app激活转化类型需要替换的参数，当想要优化其他类型时，请按照文档要求替换
         * activate: 激活
         * register: 注册
         * orders: 成单
         * user_defined: 客户自定义
         * retain_1day: 次日留存
         * highvalue_customer: 深度使用
         **/
        $url = str_replace('{{ATYPE}}', 'activate', $url);
        $url = str_replace('{{AVALUE}}', '0', $url);
        // 计算sign值
        $sign = md5($url . $akey);
        // 得到最终callback url
        $url = $url . '&sign=' . $sign;

        // 调用callback url，判断返回结果
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        Log::write("call back url for before....");
        // 添加重试，重试次数为3
        for ($i = 0; $i < 3; $i++) {
            Log::write("call back url for.....");
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            Log::write("call back url for http code...." . $httpCode);
            if ($httpCode === 200) {
                // 打印返回结果
                // do some log
                echo 'retry times: ' . $i . ' res: ' . $response . "\n";
                $res = json_decode($response, true);
                $code = $res['error_code'];
                Log::write("call back url for code...." . $code);
                // error_code为500，代表服务端异常，可添加重试
                if ($code != 500) {
                    curl_close($ch);
                    return $code === 0; // 返回error_code为0时接口调用成功，否则需排查具体原因
                }
            }
            Log::write("call back url end....");
        }
        Log::write("call back url for after....");
        curl_close($ch);
        return false;
    }


    /**
     * callbackFunc 调用callback url 向百度回传app注册转化数据
     * @param 监测地址中callback_url参数值
     * @param 当前账户对应akey
     * @return bool 成功返回true，失败返回false
     */
    function callback_func_register($callbackurl, $akey)
    {
        Log::write("callback_func_register call back url:" . $callbackurl);
        $url = urldecode($callbackurl);

        /**
         * activate: 激活
         * register: 注册
         * retain_1day: 次日留存
         **/
        $url = str_replace('{{ATYPE}}', 'register', $url);
        $url = str_replace('{{AVALUE}}', '0', $url);
        // 计算sign值
        $sign = md5($url . $akey);
        // 得到最终callback url
        $url = $url . '&sign=' . $sign;

        // 调用callback url，判断返回结果
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        Log::write("callback_func_register call back url for before....");
        // 添加重试，重试次数为3
        for ($i = 0; $i < 3; $i++) {
            Log::write("callback_func_register call back url for.....");
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            Log::write("callback_func_register call back url for http code...." . $httpCode);
            if ($httpCode === 200) {
                // 打印返回结果
                // do some log
                echo 'retry times: ' . $i . ' res: ' . $response . "\n";
                $res = json_decode($response, true);
                $code = $res['error_code'];
                Log::write("call back url for code...." . $code);
                // error_code为500，代表服务端异常，可添加重试
                if ($code != 500) {
                    curl_close($ch);
                    return $code === 0; // 返回error_code为0时接口调用成功，否则需排查具体原因
                }
            }
            Log::write("callback_func_register call back url end....");
        }
        Log::write("callback_func_register call back url for after....");
        curl_close($ch);
        return false;
    }

    /**
     * callbackFunc 调用callback url 向百度回传app次日留存转化数据
     * @param 监测地址中callback_url参数值
     * @param 当前账户对应akey
     * @return bool 成功返回true，失败返回false
     */
    function callback_func_retain_1day($callbackurl, $akey)
    {
        Log::write("retain_1day call back url:" . $callbackurl);
        $url = urldecode($callbackurl);

        /**
         * activate: 激活
         * register: 注册
         * retain_1day: 次日留存
         **/
        $url = str_replace('{{ATYPE}}', 'register', $url);
        $url = str_replace('{{AVALUE}}', '0', $url);
        // 计算sign值
        $sign = md5($url . $akey);
        // 得到最终callback url
        $url = $url . '&sign=' . $sign;

        // 调用callback url，判断返回结果
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        Log::write("retain_1day call back url for before....");
        // 添加重试，重试次数为3
        for ($i = 0; $i < 3; $i++) {
            Log::write("retain_1day call back url for.....");
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            Log::write(" retain_1day call back url for http code...." . $httpCode);
            if ($httpCode === 200) {
                // 打印返回结果
                // do some log
                echo 'retry times: ' . $i . ' res: ' . $response . "\n";
                $res = json_decode($response, true);
                $code = $res['error_code'];
                Log::write("retain_1day call back url for code...." . $code);
                // error_code为500，代表服务端异常，可添加重试
                if ($code != 500) {
                    curl_close($ch);
                    return $code === 0; // 返回error_code为0时接口调用成功，否则需排查具体原因
                }
            }
            Log::write("retain_1day call back url end....");
        }
        Log::write("retain_1day call back url for after....");
        curl_close($ch);
        return false;
    }

    public function sendData()
    {
        $token = '77WLuFIpvYm51bHKt4wGsWeFl5bDppfY@tyaduKs5rNz3Vk7ZMgkPE8bGS7WGfndO';
//        $cv = array(
//            'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',//落地页
//            'newType' => 4 // 转化类型请按实际情况填写
//        );

        $cv = [
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 4
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 5
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 26
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 28
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 49
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 52
            ],
            [
                'logidUrl' => 'https://qianhu.wejianzhan.com/site/wjz8em3w/13f96a3d-021d-4155-8b59-b8ef64a8e8c0',
                'convertType' => 61
            ],
        ];

        //此处仅为demo, conversionTypes支持添加更多数据
        $conversionTypes = array($cv);
        $this->sendConvertData($token, $conversionTypes);
    }

    /**
     * @param $token
     * @param $conversionTypes
     * @return bool 发送成功返回true，失败返回false
     */
    public function sendConvertData($token, $conversionTypes)
    {
        var_dump($conversionTypes);
        Log::write("sendConvertData.....");
        $reqData = array('token' => $token, 'conversionTypes' => $conversionTypes);
        $reqData = json_encode($reqData);

        Log::write("send convert data token param:" . $token);

        // 发送完整的请求数据
        // do some log
        print_r('req data: ' . $reqData . "\n");
        // 向百度发送数据
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, self::BAIDU_OCPC_URL);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($reqData)
            )
        );
        Log::write("send convert data curl_init()");
        // 添加重试，重试次数为3
        for ($i = 0; $i < self::RETRY_TIMES; $i++) {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Log::write("send convert data http code:" . $httpCode);
            if ($httpCode === 200) {
                Log::write("http code 符合要求！！！！");
                // 打印返回结果
                // do some log
                Log::write("执行了" . $i . "次！！！");
                print_r('retry times: ' . $i . ' res: ' . $response . "\n");
                $res = json_decode($response, true);
                // status为4，代表服务端异常，可添加重试
                $status = $res['header']['status'];
                Log::write("send convert data status:" . $status);
                if ($status !== 4) {
                    Log::write("status 符合条件！！！！");
                    curl_close($ch);
                    return $status === 0;
                }
            }
        }
        Log::write("send convert data curl_close() before....");
        curl_close($ch);
        Log::write("send convert data curl_close() after....");
        return false;
    }
}
