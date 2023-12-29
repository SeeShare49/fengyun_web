<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/4
 * Time: 18:18
 */


namespace app\common;

use tests\webw_packet_notice;
use think\facade\Log;

define("TEMP", "../extend/protobuf/");
require_once "extend/protobuf/library/DrSlump/Protobuf.php";
\DrSlump\Protobuf::autoload();


require_once "extend/protobuf/tests/protos/Recharge.php";
require_once "extend/protobuf/tests/protos/Mail.php";
require_once "extend/protobuf/tests/protos/web_ban_user.php";
require_once "extend/protobuf/tests/protos/web_add_money.php";
require_once "extend/protobuf/tests/protos/web_add_item.php";
require_once "extend/protobuf/tests/protos/Game.php";
require_once "extend/protobuf/tests/protos/shut_down_server.php";
require_once 'extend/protobuf/tests/protos/webw_packet_notice.php';


class notice_test
{
    const IP = '192.168.1.230';
    const PORT = '19107';


    /**
     * code:810
     * 公告发送
     * @param $server_id 服务器ID（字符串数组）
     * @param $content   公告内容
     */
    static function webw_packet_notice($server_id, $content)
    {
        $obj = new webw_packet_notice();
        $obj->setGameId($server_id);
        $obj->setContent($content);
        $code = 810;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:263
     * 用户登录
     *
     * @param $name
     * @param $password
     * @param $type         登录类型（pc端，手机端，模拟器）
     * @param $channel_id   渠道ID
     * @param $machinecode
     * @param $ip
     */
    static function contents($name, $password, $type, $channel_id, $machinecode, $ip)
    {
        $obj = new \tests\Contents();
        $json_data = '{ "name": ' . $name . ',"password": ' . $password . ',"authtype": ' . $type . ',"channelid": ' . $channel_id . "machinecode" . $machinecode . ',"ip": "' . $ip . '" }';

        $strcontent = $json_data;
        $obj->setContens($strcontent);
        $code = 263;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }


    /**
     * 组合发送的数据包
     *
     * @param $ip   IP地址
     * @param $port 端口号
     * @param $obj  数据对象
     * @param $code 命令码
     */
    static function combin_pack($ip, $port, $obj, $code)
    {
        $first_data = pack("s*", $code);
        $first_len = 4;
        count(array($obj));
        if (is_array($obj)) {
            \think\facade\Log::write("当前obj为数组");
        } else {
            \think\facade\Log::write("非数组");
        }
        $second_data = $obj->serialize();//序列化
        // $second_data=  serialize($obj);
        $second_len = strlen($second_data);
        $totallen = $first_len + $second_len;
        $totallen_data = pack("s*", $totallen);
        $second_pack = $second_data;//长度 协议 内容（protobuf）
        $pack = $totallen_data . $first_data . $second_pack;
        self::send_msg($ip, $port, $pack);
    }


    /**
     * 发送消息
     *
     * @param $ip   ip地址
     * @param $port 端口号
     * @param $pack 数据包
     */
    static function send_msg($ip, $port, $pack)
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            Log::write("socket_create() failed: reason: " . socket_strerror($socket) . "\n");
        }
        $result = @socket_connect($socket, $ip, $port);
        if ($result < 0) {
            Log::write("socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n");
        } else {
            Log::write("ip:'$ip',port:'$port'连接成功!!!");
        }

        if (!@socket_write($socket, $pack, strlen($pack))) {
            Log::Write("socket_write() failed: reason: " . @socket_strerror($socket) . "\n");
        } else {
            Log::write("发送到服务器信息成功！\n发送的内容为:'$pack'");
        }
        sleep(1);

        //while ($out = socket_read($socket, 8192)) {
        //echo "接收服务器回传信息成功！\n";
        //                echo "接受的内容为:", $out;
        //            }

        Log::write("关闭SOCKET...\n");
        @socket_close($socket);
        Log::write("关闭OK\n");
    }

}