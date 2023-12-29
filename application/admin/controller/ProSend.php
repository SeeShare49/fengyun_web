<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 18:41
 */


use app\common\test;

define("TEMP", "../");
$path = TEMP . "Extend/protobuf/library/DrSlump/Protobuf.php";
var_dump(realpath($path));
require_once $path;
\DrSlump\Protobuf::autoload();


require_once TEMP . "Extend/protobuf/tests/protos/Recharge.php";
require_once TEMP . "Extend/protobuf/tests/protos/Game.php";
require "function.php";

class ProSend
{
    public function index()
    {

        $obj = new \tests\Recharge();
        $obj->setGameId(1001);
        $obj->setRechargeId("100023123569553");


        $first_data = pack("s*", 802);
        $first_len = 4;//pack("s*",4);

        $second_data = $obj->serialize();//序列化
        $second_len = strlen($second_data);// pack("I*",strlen($second_data));
        $totallen = $first_len + $second_len;

        $totallen_data = pack("s*", $totallen);
        $second_pack = $second_data;//长度 协议 内容（protobuf）

        //测试服IP
        $ip = config('admin.SOCKET_SERVER_IP');
        $port = "19107";
        $pack = array($totallen_data, $first_data, $second_pack);

        $result = @socket($ip, $port, $pack);//连接 发送 接受数据  数据为长度 协议 内容(protobuf）
    }

    public function game_login($name, $password, $type, $channel_id, $machinecode, $ip)
    {
        $data = '{"opcode":149,"content":{"password":"E10ADC3949BA59ABBE56E057F20F883E","channelid":1,"machinecode":"f4-b5-20-21-4a-36","name":"44444444444","authtype":1}}';
        if (!empty($data)) {
            $content = json_decode($data, true);
            $name = '';
            $ip = '192.168.1.230';
            app\common\test::contents($name, $content['password'], $content['authtype'],  $content['channelid'], $content['machinecode'], $ip);
        }
        //contents($name, $password, $type, $channel_id, $machinecode, $ip)

        //$obj = new \tests\Contents()
        //common\test::contents($data['server_id'], $data['player_name'], $data['title'], $data['item_id'], $data['item_count']);
    }
}