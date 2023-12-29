<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 11:19
 */

namespace app\admin\controller;

$path = "Socket.php";


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
        $this->log_error(" error send to server" . $e->getMessage());
    }
    return $getrepost;
}


function unPackData_1($data)
{
    $rev_len = unpack("L*", substr($data, 0, 4));
    $rev_num = unpack("S*", substr($data, 4, 2));
    var_dump("rev num:" . $rev_num);
    $rev_data = substr($data, 4, 2);

    $data_array = array(
        'num' => $rev_num,
        'data' => $rev_data
    );
    return $data_array;

}

function unPackData($data, $len)
{
    if ($len >= 4) {
        $rev_len = unpack("S*", substr($data, 0, 2));

        $rev_num = unpack("S*", substr($data, 2, 2));
        $flg = $rev_len[1] - 4;

        $rev_data = substr($data, 4, $flg);
//        $rev_data = substr($data, intval($rev_len) + intval($rev_num), intval($len) - intval($rev_len));


        $data_array = array(
            'data' => $rev_data
        );
        return $data_array;
    }
}

