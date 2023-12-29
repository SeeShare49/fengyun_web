<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/17
 * Time: 10:58
 */

namespace app\common;

use think\facade\Log;

class HttpSend
{
    /**
     * 发送HTTP请求
     * @param string $url 请求地址
     * @param array $data 发送数据
     * @param string $method 请求方式 GET/POST
     * @param string $timeout
     * @param string $proxy
     * @return Json
     */
    static function send_request($url, $data, $method = 'POST', $timeout = 30, $proxy = false)
    {
        $ch = null;
        if ('POST' === strtoupper($method)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
// 			if ($refererUrl) {
// 				curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
// 			}
// 			if($contentType) {
// 				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:'.$contentType));
// 			}
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } else if ('GET' === strtoupper($method)) {
            if (is_string($data)) {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . $data;
            } else {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($data);
            }
            $ch = curl_init($real_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
// 			if($contentType) {
// 				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:'.$contentType));
// 			}
// 			if ($refererUrl) {
// 				curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
// 			}
        } else {
            $args = func_get_args();
            return false;
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $ret = curl_exec($ch);
// 		$info = curl_getinfo($ch);
// 		$contents = array(
// 			'httpInfo' => array(
// 					'send' => $data,
// 					'url' => $url,
// 					'ret' => $ret,
// 					'http' => $info
// 			)
// 		);
        curl_close($ch);
        return $ret;
    }

    static function curPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);//不抓取头部信息。只返回数据
        curl_setopt($curl, CURLOPT_TIMEOUT, 1000);//超时设置
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//1表示不返回bool值
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Content-Length: ' . strlen($data)));
        if (is_string($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return curl_error($curl);

        }
        curl_close($curl);
        return $response;
    }

    /**
     * 发送请求方法
     *
     * @param string $url
     * @param array $postData
     * @return array
     */
    public static function curlPostSend($url, $postData)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置返回值存储在变量中
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $header = array('Expect:');
        $header[] = "Content-type:application/x-www-form-urlencoded";
//        $header[] = "User-Agent:x7sdk-php-2021";
//        if (is_string($postData)) {
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
//        } else {
//            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
//        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $output = curl_exec($ch);
        $httpStateArr = curl_getinfo($ch);
        curl_close($ch);
        Log::write("out put :".$output);
//        return array($httpStateArr["http_code"], $output);
        return $output;
    }
}