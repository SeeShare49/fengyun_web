<?php


namespace app\pay\common;

use think\Exception;
use think\facade\Log;

define('APP_SECRET',config('pay.wx_pay.AppSecret'));
class WxPayUtil
{
    public $key = APP_SECRET;
    //xml请求
    public function postXmlCurl($url, $xml, $second = 30)
    {
        $header = $this->FormatHeader($url);
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        //设置 header

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post 提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        //运行 curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            Log::write("post xml curl return data:" . $data);
            return $data;
        } else {
            Log::write("curl error code:".curl_errno($ch));
            $error = curl_errno($ch);
            curl_close($ch);
            Log::write("curl error code:".$error);
            echo "curl 出错，错误码:$error" . "<br>";
        }
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    function ToXml($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            throw new Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($params as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 生成签名
     * @return 签名
     */
    public function MakeSign($params)
    {
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        Log::write("make sign str:" . $string);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */


    /**
     * 将参数拼接为url: key=value&key=value
     * @param   $params
     * @return  string
     */
    public function ToUrlParams($params)
    {
        $string = '';
        if (!empty($params)) {
            $array = array();
            foreach ($params as $key => $value) {
                $array[] = $key . '=' . $value;
            }
            $string = implode("&", $array);
        }
        return $string;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_data($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }


//添加请求头
    function FormatHeader($url)
    {
        // 解析url
        $temp = parse_url($url);
        $query = isset($temp['query']) ? $temp['query'] : '';
        $path = isset($temp['path']) ? $temp['path'] : '/';
        $header = array(
            "POST {$path}?{$query} HTTP/1.1",
            "Host: {$temp['host']}",
            "Referer: http://{$temp['host']}/",
            "Content-Type: application/json; charset=utf-8",
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding:gzip, deflate, br',
            'Accept-Language:zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
            'Connection:keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0',
            'X-Requested-With: XMLHttpRequest',
        );
        return $header;
    }
}