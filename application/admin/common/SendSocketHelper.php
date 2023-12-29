<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 16:22
 */


$selfPath = dirname(__FILE__);
require($selfPath . "/Bytes.php");
require($selfPath . "/Bytes2.php");
require($selfPath . "/Socket.php");


class SendSocketHelper
{
    function send($msg)
    {
        try {
            //测试服IP
            $ip = config('admin.SOCKET_SERVER_IP');
            //正式服IP
            //$ip = '';
            $port = "19107";
            $socket = Socket1::singleton();
            $socket->connect($ip, $port); //连服务器

            $sockResult = $socket->sendRequest($msg); // 将包发送给服务器
            if (!$sockResult) {
                exit();
            }

            sleep(3);
            $socket->disconnect(); //关闭链接
        } catch (Exception $e) {
            $this->log_error("message send to server" . $e->getMessage());
        }
    }
}