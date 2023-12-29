<?php

namespace app\common;


define("TEMP", "../../Extend/protobuf/");
require_once TEMP . "library/DrSlump/Protobuf.php";
\DrSlump\Protobuf::autoload();


require_once TEMP . "tests/protos/Recharge.php";
require_once TEMP . "tests/protos/Mail.php";
require_once TEMP . "tests/protos/web_ban_user.php";
require_once TEMP . "tests/protos/web_add_money.php";
require "function.php";


class ProtocSend
{

    public function send($ip,$port,$code,$data)
    {
        $first_data = pack("s*", $code);

        $first_len = 4;//pack("s*",4);

        $second_data = $data->serialize();//序列化
        $second_len = strlen($second_data);// pack("I*",strlen($second_data));
        $totallen = $first_len + $second_len;

        $totallen_data = pack("s*", $totallen);
        $second_pack = $second_data;//长度 协议 内容（protobuf）


        $pack = array($totallen_data, $first_data, $second_pack);

        $result = @socket($ip, $port, $pack);//连接 发送 接受数据  数据为长度 协议 内容(protobuf）
    }
}
