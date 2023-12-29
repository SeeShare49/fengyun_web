<?php

namespace app\admin\controller;

use app\common\ServerManage;
use app\common\test;
use Google\Protobuf\Internal\RepeatedField;
use GPBMetadata\Google\Protobuf\Struct;
use tests\web_packet_chat;
use think\Db;
use think\facade\Log;
use think\facade\View;

define("TEMP", "../extend/protobuf/");
require_once TEMP . "library/DrSlump/Protobuf.php";

\DrSlump\Protobuf::autoload();

require_once TEMP . 'tests/protos/web_packet_chat.php';


require_once 'function.php';

define('DB_HOST', config('admin.DB_HOST'));

class Chat extends Base
{
    public $host = DB_HOST;
    public $port = 40011;
    public $chatArr = [];

    public function index()
    {
        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        View::assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'meta_title' => '实时聊天记录查询'
        ]);
        return View::fetch();
    }

    public function receive($server_id)
    {
        if ($server_id) {
            $file_path = dirname(__FILE__) . "/chat_file.txt";
//        if(file_exists($file_path)){
//            $fp = fopen($file_path,"r");
//            $str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
//
//            $str =     strstr($str, '{');
//
//            echo $str = str_replace("\r\n","<br />",$str);
//        }
            $read_info = $this->readFile($file_path);
            foreach ($read_info as $key => $value) {
                if (!empty($value)) {
                    $unPackDatas = unPackData($value, strlen($value));

                    $count_length = 0;
                    $type = unpack("i*", substr($unPackDatas['data'], 0, 4));
                    $count_length += 4;

                    $msg_length = unpack("s*", substr($unPackDatas['data'], $count_length, 2));
                    $count_length += 2;
                    $msg = unpack("a*", substr($unPackDatas['data'], $count_length, $msg_length[1]));
                    $count_length += $msg_length[1];

                    $from_length = unpack("s*", substr($unPackDatas['data'], $count_length, 2));
                    $count_length += 2;
                    $from = unpack("a*", substr($unPackDatas['data'], $count_length, $from_length[1]));
                    $count_length += $from_length[1];

                    $to_length = unpack("s*", substr($unPackDatas['data'], $count_length, 2));
                    $count_length += 2;
                    $to = unpack("a*", substr($unPackDatas['data'], $count_length, $to_length[1]));
                    $count_length += $to_length[1];

                    $s_id = unpack("I*", substr($unPackDatas['data'], $count_length, 4));

                    if ($server_id == $s_id[1]) {
                        //追加至数组中（chat_array[]）
                        if (!empty($msg[1])) {
                            $json_msg = json_decode($msg[1], true);
                            $json_msg_count = count($json_msg);
                            $msg_str = $json_msg['1']['sContent'];
//                        for ($i = 0; $i < $json_msg_count; $i++) {
//                            $msg_str .= $json_msg[''.$i.'']['sContent'] . '|';
//                        }
                            if (!empty($msg_str)) {
                                $this->get_chat_list($type[1], $msg_str, $from[1], $to[1], $s_id[1]);
//                        $this->get_chat_list($type[1], $msg[1], $from[1], $to[1], $s_id[1]);
                            }
                        }
                    }
                }
            }
            View::assign([
                'chat_list' => $this->chatArr
            ]);
            return View::fetch();
        } else {
            $this->error("你是要逆天么?请选择区服,呆逼!!!");
        }
    }


    /**
     * 读取文件内容
     * @param $file_path
     * @return \Generator
     */
    function readFile($file_path)
    {
        $handle = fopen($file_path, 'rb');
        while (feof($handle) === false) {
            yield fgets($handle);
        }
        fclose($handle);
    }

    /**
     * 追加聊天记录到数组
     * @param $type
     * @param $msg
     * @param $from
     * @param $to
     * @param $server_id
     * @return int
     */
    public function get_chat_list($type, $msg, $from, $to, $server_id)
    {
//        $data = json_decode($msg, true);
//        $msg = $data[1]['sContent'];


        //过滤用户发送的聊天消息，发送对用户（永久）禁言操作
        $filter_str = disable_user_filter_str(trim($msg));

        if (!empty($filter_str)) {
            $msg = str_replace($filter_str, "<font color=#FF0000><b>$filter_str</b></font>", $msg);
            $userInfo = dbConfig($server_id)->table('player')->where('nickname', '=', trim($from))->find();
            if ($userInfo) {
                //禁言发送命令TODO:
                //webw_packet_forbidden_chat
                test::webw_packet_forbidden_chat($server_id, $userInfo['nickname'], true);
            }
        }

        $chat['type'] = $type;
        $chat['msg'] = $msg;
        $chat['from'] = $from;
        $chat['to'] = $to;
        $chat['server_id'] = $server_id;
        return array_push($this->chatArr, $chat);
    }
}
