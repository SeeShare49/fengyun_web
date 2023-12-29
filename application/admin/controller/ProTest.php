<?php

namespace app\admin\controller;


define("TEMP", "../Extend/protobuf/");
require_once TEMP . "library/DrSlump/Protobuf.php";
\DrSlump\Protobuf::autoload();


use tests\Contents;
use think\Controller;

use think\facade\Log;

require_once TEMP . "tests/protos/Recharge.php";
require_once TEMP . "tests/protos/Mail.php";
require_once TEMP . "tests/protos/web_ban_user.php";
require_once TEMP . "tests/protos/web_add_money.php";
require_once TEMP . "tests/protos/Game.php";

require_once "function.php";

class ProTest extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //$data = $json;
        // $data='{"opcode":149,"content":{"password":"E10ADC3949BA59ABBE56E057F20F883E","channelid":1,"machinecode":"18-c0-4d-1b-94-28","name":"44444444444","authtype":1}}';

        $data = '{"opcode":149,"content":{"password":"E10ADC3949BA59ABBE56E057F20F883E","channelid":1,"machinecode":"f4-b5-20-21-4a-36","name":"44444444444","authtype":1}}';
        dump($data);
        Log::write($data);
        $recieve_json_data = json_decode($data, true);

        /**
         *
         * //'json' => '{"opcode":149,"content":{"password":"E10ADC3949BA59ABBE56E057F20F883E","channelid":1,"machinecode":"18-c0-4d-1b-94-28","name":"44444444444","authtype":1}}',
         *
         * //        $obj = new \tests\Recharge();
         * //        $obj->setGameId(1001);
         * //        $obj->setRechargeId("10002312356955300");
         *
         *
         * //        $obj = new \tests\Mail();
         * //        $obj->setGameId(999);
         * //        $name ="天下无敌";
         * //        //$name = iconv('UTF-8','GB2312',  $name);
         * //        $obj->setPlayerName($name);
         * //        $title ="我是标题";
         * //        //$title = iconv('UTF-8','GB2312',  $title);
         * //        $obj->setTitle("$title");
         * //        $content ="我是内容";
         * //        //$content = iconv('UTF-8','GB2312',  $content);
         * //        $obj->setContent($content);
         * //        $mail = new \tests\Mail\mail_item();
         * //        $mail->setItemId(11);
         * //        $mail->setItemCount(20);
         * //        $obj->setMailItem($mail);
         * //        $first_data = pack("s*", 804);
         *
         *
         * //        $obj = new \tests\web_ban_user();
         * //        $obj->setUserId("1000000000000999");
         * //        $obj->setBan(false);
         * //        $obj->setReason("10000");
         * //        $first_data = pack("s*", 803);
         * //
         * //        $obj = new \tests\web_add_money();
         * //        $obj->setGameId(99999);
         * //        $obj->setPlayerName("天下第一");
         * //        $obj->setYuanBao(10000);
         * //        $obj->setGold(59999);
         * //        $obj->setDiamonds(19);
         * //        $first_data = pack("s*", 801);
         * //
         * //app\common\test::contents($name, $password, $type, $channel_id, $machinecode, $ip);
         * //$name, $password, $type, $channel_id, $machinecode
         * */


        $obj = new \tests\Contents();

        //$json_data = '{ "name": "' . $recieve_json_data['content']['name'] . '","password": "' . $recieve_json_data['content']['password'] . '","authtype": "'
        //. $recieve_json_data['content']['authtype'] . '","channelid": "' .
        //$recieve_json_data['content']['channelid'] . '","machinecode":"' . $recieve_json_data['content']['machinecode'] . '","ip": "' . get_ip() . '" }';
        //var_dump($json_data);
        //接收到的参数:'json' => '{"opcode":149,"content":{"password":"E10ADC3949BA59ABBE56E057F20F883E","channelid":1,"machinecode":"18-c0-4d-1b-94-28","name":"44444444444","authtype":1}}',

        // $strcontent = $json_data;
        // $strcontent = '{"opcode":' . $recieve_json_data['opcode'] . ',"content":' . $json_data . '}';
        $strcontent = $data;

        // $gameitem = new \tests\Contents\Game();
        // $gameitem->setType(1222);
        // $gameitem->setChannelId(111);
        // $gameitem->setPhone("13858868309");
        // $obj->setGameItem($gameitem);
        $obj->setContens($strcontent);
        var_dump($obj);
        $first_data = pack("s*", 263);


        //pack("s*",4);
        $first_len = 4;
        $second_data = $obj->serialize();//序列化
        var_dump($second_data);
        // pack("I*",strlen($second_data));
        $second_len = strlen($second_data);
        $totallen = $first_len + $second_len;

        $totallen_data = pack("s*", $totallen);
        $second_pack = $second_data;//长度 协议 内容（protobuf）

        $ip = config('admin.SOCKET_SERVER_IP');
        //$port = "19107";
        $port = "9070";
        //$pack = array($totallen_data, $first_data, $second_pack);
        $pack = $totallen_data . $first_data . $second_pack;
        //$result = @socket($ip, $port, $pack);//连接 发送 接受数据  数据为长度 协议 内容(protobuf）

        $this->send_msg($ip, $port, $pack);
    }


    /**
     * 获取客户端请求IP
     */
    function get_ip()
    {
        $unknown = 'unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        /*
        处理多层代理的情况
        或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
        */
        if (false !== strpos($ip, ',')) {
            $array = explode(',', $ip);
            $ip = reset($array);
        }
        Log::write("获取到的IP:" . $ip);
        echo $ip;
        return $ip;
    }


    /**
     * 发送socket数据
     * @param $ip
     * @param $port
     * @param $pack
     */
    function send_msg($ip, $port, $pack)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
        } else {
            echo "OK.\n";
        }

        echo "试图连接 '$ip' 端口 '$port'...\n";
        $result = socket_connect($socket, $ip, $port);
        if ($result < 0) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
        } else {
            echo "连接OK\n";
        }

        if (!socket_write($socket, $pack, strlen($pack))) {
            echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
        } else {
            echo "发送到服务器信息成功！\n";
            echo "发送的内容为:<font color='red'>$pack</font> <br>";
        }
        sleep(3);

        while ($out = socket_read($socket, 8192)) {
            echo "接收服务器回传信息成功！\n";

            echo "接受的内容为:", substr($out,6);
//            $this->BinToStr($out);
//            var_dump($this->BinToStr($out));
            //$this->unPackData($out, strlen($out));

            Log::write("接受的out内容为:" . $out);
            //Log::write("接受的unPackData out内容为:" . $this->unPackData($out, strlen($out)));
            socket_close($socket);
            return substr($out,6);
        }

        echo "关闭SOCKET...\n";
        socket_close($socket);
        echo "关闭OK\n";
    }


    /**
     * 将二进制转换成字符串
     * @param type $str
     * @return type
     */
    function BinToStr($str)
    {
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }

    function unPackData($data, $len)
    {
        $rev_len = unpack("L*", substr($data, 0, 2));
         $rev_num = unpack("S*", substr($data, 4, 2));
         //intval($a)+intval($b);
        $rev_data = substr($data, 6, $len - $rev_len);
       // $rev_data = substr($data, 4, $len - $rev_len);

        $data_array = array(
            //'num' => $rev_num,
            'data' => $rev_data
        );
        return $data_array;
    }

    function dfa_test()
    {

    }

}


