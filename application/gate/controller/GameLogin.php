<?php

namespace app\gate\controller;


use app\common\test;
use think\facade\Log;
use think\facade\Request;

class GameLogin
{
    public function index()
    {
//        {"opcode":149,"content":{"password":"","uid":"JingTianDongDiqd21046409","channelid":2,"machinecode":"863519041778411","sdkchannelname":"JingTianDongDi","name":"JingTianDongDiqd21046409","authtype":0}}'
//        {"opcode":149,"content":{"password":"","channelid":1,"machinecode":"02-00-4c-4f-4f-50","name":"02-00-4c-4f-4f-50","authtype":0}}'
        if (request()->isPost()) {
            $data = $_POST;
            if (!empty($data)) {
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
        } else {
            return "{\"code\": -1,\"msg\": \"非法请求\",\"data\": ''}";
        }
    }
//
//    public function index()
//    {
//        if (Request::isPost()) {
//            $data = $_POST;
//            Log::write("game gate login request data: " . $data);
//            if (!empty($data)) {
//                $ip = config('admin.SOCKET_SERVER_IP');
//                Log::write('game gate login request ip:' . $ip);
//                Log::write('game gate login request data json:' . $data['json']);
//                test::contents($ip, $data['json']);
//            }
//        } else {
//            return "{\"code\": -1,\"msg\": \"非法请求\",\"data\": ''}";
//        }
//    }

    /**
     * socket返回消息内容
     **/
    public function receive()
    {
        Log::write("game web receive callback....");
    }
}
