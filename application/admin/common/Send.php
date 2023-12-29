<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 13:39
 */

$selfPath = dirname(__FILE__);
require($selfPath . "/Bytes.php");
require($selfPath . "/Socket.php");

//class Send
//{
//    public function index()
//    {
//测试服IP
$socketAddr = config('admin.SOCKET_SERVER_IP');
$socketPort = "19107";

try {
    $bytes = new Bytes();


    $game_id = 56;
    $recharge_id = 190000000002;
    $strLen = strlen($recharge_id);
    $headType = 802;
    $headLength = 10 + $strLen;

    $headLength = $bytes->shortToBytes(intval($headLength));
    var_dump("headlength:" . $headLength);
    $headType = $bytes->shortToBytes(intval($headType));
    $game_id = $bytes->integerToBytes(intval($game_id));
    $srecharge_id = strval($recharge_id);
    $strLen = $bytes->shortToBytes(strlen($srecharge_id));

    $recharge_id = $bytes->getBytes($srecharge_id);
    $return_betys = array_merge($headLength, $headType, $game_id, $strLen, $recharge_id);


    $msg = $bytes->toStr($return_betys);
    $strLen = strlen($msg);

    $packet = pack("a{$strLen}", $msg);
    $pckLen = strlen($packet);

    $socket = Socket1::singleton();
    $socket->connect($socketAddr, $socketPort); //连服务器

    $sockResult = $socket->sendRequest($packet); // 将包发送给服务器
    sleep(3);
    $socket->disconnect(); //关闭链接
} catch (Exception $e) {
    $this->log_error("pay order send to server" . $e->getMessage());
}
//    }
//}