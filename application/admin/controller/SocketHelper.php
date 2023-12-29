<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 15:47
 */

require 'Socket.php';

class SocketHelper
{

    function socket($ip, $port, $packed)
    {
        try {
            $socket = Socket::singleton();
            $socket->connect($ip, $port);
            if (count($packed) > 1) {
                foreach ($packed as $key) {
                    $sockResult = $socket->sendRequest($key);// 将包发送给服务器
                    sleep(3);
                }
            }
            $getrepost = $socket->getResponse();

            $socket->disconnect(); //关闭链接
        } catch (Exception $e) {
            var_dump($e);
            $this->log_error(" error send to server" . $e->getMessage());
        }
        return $getrepost;
    }

    function unPackData($data)
    {
        $rev_len = unpack("L*", substr($data, 0, 4));
        $rev_num = unpack("S*", substr($data, 4, 2));
        $rev_data = substr($data, 6, 2);

        $data_array = array(
            'num' => $rev_num,
            'data' => $rev_data
        );
        return $data_array;
    }
}
