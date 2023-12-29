<?php

namespace app\api\controller;

use app\common\test;
use think\facade\Log;
use think\facade\Request;

class Gate
{
    public function test2()
    {
//        $ret_Arr = array(
//            'json' => '{"opcode":149,"content":{"password":"","channelid":"1","machinecode":"f4-b5-20-21-4a-0e","sdkchannelname":"tianyu","name":"f4-b5-20-21-4a-0e","authtype":0}}',
//        );

        $ret_Arr = Request::post();

        Log::write("server before url:". $_SERVER['REQUEST_URI']);

        $request_url = 'http://guild3.52yiwan.cn/gate.php/GameLogin/index2';
//        $request_url = 'http://192.168.1.153:86/gate.php/GameLogin/index2';
        Log::write("server after url:". $_SERVER['REQUEST_URI']);
        dump(json_encode($ret_Arr, true));
        dump("ret array json:" . $ret_Arr['json']);
        Log::write("ret array json:" . $ret_Arr['json']);

        $req_Arr = array(
            'json'=>$ret_Arr['json']
        );

        $this->http_request_json($request_url,json_encode( $req_Arr['json'],true));
        Log::write("==========================");
        Log::write("gate test2 end....");
    }

    public function test1()
    {
        $ret = array(
            'opcode' => 149,
            'content' =>
                array(
                    'password' => '',
                    'channelid' => '1',
                    'machinecode' => 'f4-b5-20-21-4a-0e',
                    'sdkchannelname' => 'tianyu',
                    'name' => 'f4-b5-20-21-4a-0e',
                    'authtype' => 0,
                ),
        );
        var_dump(json_encode($ret));
    }

    public function index()
    {
        Log::write("网络接口被调用....");
        if (Request::isPost()) {
            $data = $_POST;
            if (!empty($data)) {
                Log::write("data json:" . $data['json']);
                $res = json_decode($data['json'], true);
                if (!empty($res)) {
                    $sdk_channel_name = $res['content']['sdkchannelname'];
                    Log::write("sdk channel name:" . $sdk_channel_name);
                    if (isset($sdk_channel_name) && !empty($sdk_channel_name)) {
                        //http://guild3.52yiwan.cn   ----HTC、坚果、大秦、天宇游
                        switch (trim($sdk_channel_name)) {
                            case 'jule':
                            case 'jianguo':
                            case 'daqin':
                            case 'tianyu':
                                $request_url = 'http://guild3.52yiwan.cn/gate.php/GameLogin/index';
                                break;
                            default:
                                $request_url = '';
                                break;
                        }

                        Log::write("request url:" . $request_url);

                        if (empty($request_url)) {
                            return json(['code' => false, 'msg' => '网关接口分发请求Url为空!']);
                        }
                        Log::write("请求开始。。。。。。");
                        $ret_Arr = array(
                            'json' => '' . $data['json'] . '',
                        );
                        $this->http_request_json($request_url, json_encode($ret_Arr, true));
                        Log::write("请求完毕。。。。。。");
                    } else {
                        return json(['code' => false, 'msg' => '网关接口请求,渠道名称' . [$sdk_channel_name] . '参数错误!']);
                    }
                }
            }
        } else {
            return json(['code' => false, 'msg' => '网关接口请求参数错误!']);
        }
    }


    public function redirect_url($data)
    {
        if (!empty($data)) {
            Log::write("data json:" . $data['json']);
            $res = json_decode($data['json'], true);
            if (!empty($res)) {
                $channelid = $res['content']['channelid'];
                if (intval($channelid) >= 100) {
                    $ip = config('admin.NEW_SOCKET_SERVER_IP');
                } else {
                    $ip = config('admin.SOCKET_SERVER_IP');
                }
            } else {
                $ip = config('admin.SOCKET_SERVER_IP');
            }
            test::contents($ip, $data['json']);
        }
    }

    public function test()
    {
        $ret_Arr = array(
            'json' => '{"opcode":149,"content":{"password":"","channelid":"1","machinecode":"f4-b5-20-21-4a-0e","sdkchannelname":"tianyu","name":"f4-b5-20-21-4a-0e","authtype":0}}',
        );
        $request_url = 'http://guild3.52yiwan.cn/gate.php/GameLogin/index';
        dump(json_encode($ret_Arr, true));
        dump("ret array json:" . $ret_Arr['json']);
        // $ret_str = $this->http_request_json($request_url, json_encode($ret_Arr,true));;
        $ret_str = $this->http_request_json($request_url, $ret_Arr['json']);;
        echo $ret_str;
    }

    // HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    function http_request_json($url, $data)
    {
        Log::write("http_request_json request......");
        Log::write("url:" . $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //设置头部信息
        $headers = array('Content-Type:application/json; charset=utf-8', 'Content-Length: ' . strlen($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //执行请求
        $output = curl_exec($ch);
    }


}
