<?php

namespace app\api\controller;

use app\admin\model\SendSms;
use app\admin\model\UserInfo;
use app\common\HttpSend;
use think\facade\Log;

class SendMessage
{
//    const SEND_URL = "http://smssh1.253.com/msg/v1/send/json";
    const SEND_URL = 'http://smssh1.253.com/msg/send/json';
    const ACCOUNT = 'N1211274';
    const PASSWORD = 'lfvs3QSGTV873c';
    const SERVER = '王者服';

    public function sendSms()
    {
        $user_count = UserInfo::count();
        $phone_lists = '18060120459,19957464423,13787279047';
        $curr_time = date('Y-m-d', time());
        $start_time = strtotime($curr_time . ' 00:00:00');
        $end_time = strtotime($curr_time . ' 23:59:59');
        $where[] = ['send_time', 'between', [$start_time, $end_time]];
        $record = SendSms::where($where)->select();
        $insert_arr = array();
        if (count($record) == 0) {
            if ($user_count > 400 || $user_count >= 450) {
                $sendMsg['account'] = self::ACCOUNT;
                $sendMsg['password'] = self::PASSWORD;
                $sendMsg['msg'] = '【龙腾天下】-【' . self::SERVER . '】' . '注册用户数为【' . $user_count . '】,请移步相应处理!';
                $sendMsg['phone'] = $phone_lists;
                $sendMsg['sendtime'] = date('YmdHi', time());

                $phone_arr = explode(',', $phone_lists);
                foreach ($phone_arr as $key => $value) {
                    $data['telphone'] = $value;
                    $data['send_msg'] = $sendMsg['msg'];
                    $data['send_time'] = time();
                    array_push($insert_arr, $data);
                }
                SendSms::insertAll($insert_arr);

                return $this->curlPost(self::SEND_URL, $sendMsg);
            } else {
                Log::write('截止【' . date("Y-m-d H:i:s") . '】,【' . self::SERVER . '】,用户注册数:【' . $user_count . '】');
                echo self::SERVER . '用户注册数:' . $user_count;
            }
        }
    }

    /**
     * 通过CURL发送HTTP请求
     * @param $url
     * @param $postFields
     * @return bool|string
     */
    function curlPost($url, $postFields)
    {
        $postFields = json_encode($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态" . $rsp . "" . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        return $result;
    }
}
