<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/10/20
 * Time: 15:06
 */

namespace app\api\controller;

use app\common\HttpSend;
use think\facade\Log;

/**
 * 敏感词汇过滤类
 */
class SensitiveWord
{
    static function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);
        return $data;
    }

    public function get_access_token()
    {
        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        Log::write("get access token start....");
        $data['grant_type'] = 'client_credentials';
        $data['client_id'] = config('api.Access_Key');
        $data['client_secret'] = config('api.Secret_Key');

        $data = HttpSend::send_request($url, $data, 'GET');
        Log::write("get access token return data:" . $data);
        if (isset($data)) {
            $data = json_decode($data, true);
            $access_token = $data['access_token'];

            return $access_token;
        }
        return '';
    }

    public function valid_word($word)
    {
        $access_token = $this->get_access_token();;
        Log::write("valid word access token:" . $access_token);

        $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined';
//        $token = '24.b5eac7e1c98fab8fdc6e43a829122f21.2592000.1637307078.282335-25024021';
        $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined?access_token=' . $access_token;
        $bodys = array(
            'text' => $word
        );
        $res = self::request_post($url, $bodys);
        Log::write("valid word res:" . $res);
        return $res;
    }

    public function test()
    {
        $str_json = '{"refresh_token":"25.04aaa4dee9d0e547c0516787588ab39d.315360000.1950075846.282335-25024021","expires_in":2592000,"session_key":"9mzdCy58xNN+KRcuY3gOO6QVhsDQB5JVhPgERFLtkv\/euFJKPKX10yjSDllxO58CgqaZaTaHAOqJpipj2Dp51SebpnzIxg==","access_token":"24.06bf034b3b7ff29b23b26e8e04345b18.2592000.1637307846.282335-25024021","scope":"public brain_all_scope solution_face wise_adapt lebo_resource_base lightservice_public hetu_basic lightcms_map_poi kaidian_kaidian ApsMisTest_Test\u6743\u9650 vis-classify_flower lpq_\u5f00\u653e cop_helloScope ApsMis_fangdi_permission smartapp_snsapi_base smartapp_mapp_dev_manage iop_autocar oauth_tp_app smartapp_smart_game_openapi oauth_sessionkey smartapp_swanid_verify smartapp_opensource_openapi smartapp_opensource_recapi fake_face_detect_\u5f00\u653eScope vis-ocr_\u865a\u62df\u4eba\u7269\u52a9\u7406 idl-video_\u865a\u62df\u4eba\u7269\u52a9\u7406 smartapp_component smartapp_search_plugin avatar_video_test b2b_tp_openapi","session_secret":"fc7b14d6914197e39d8df19265f01f25"} ';
        $str_json = json_decode($str_json, true);
        var_dump($str_json['access_token']);
    }
}