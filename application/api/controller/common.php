<?php
header("Content-Type:text/html;charset=utf-8");
function ConvertPublicKey($public_key)
{
    $public_key_string = "";
    $count = 0;
    for ($i = 0; $i < strlen($public_key); $i++) {
        if ($count < 64) {
            $public_key_string .= $public_key[$i];
            $count++;
        } else {
            $public_key_string .= $public_key[$i] . "\r\n";
            $count = 0;
        }
    }
    $public_key_header = "-----BEGIN PUBLIC KEY-----\r\n";
    $public_key_footer = "\r\n-----END PUBLIC KEY-----";
    $public_key_string = $public_key_header . $public_key_string . $public_key_footer;
    return $public_key_string;
}

function Verify($sourcestr, $sign_dataature, $publickey)
{
    $pkeyid = openssl_get_publickey($publickey);
    $verify = openssl_verify($sourcestr, $sign_dataature, $pkeyid);
    openssl_free_key($pkeyid);
    return $verify;
}

function PublickeyDecodeing($crypttext, $publickey)
{
    $pubkeyid = openssl_get_publickey($publickey);
    if (openssl_public_decrypt($crypttext, $sourcestr, $pubkeyid, OPENSSL_PKCS1_PADDING)) {
        return $sourcestr;
    }
    return FALSE;
}

function ReturnResult($text)
{
    echo $text;
    exit();
}

function PingErrorRecorder($recorderData, $is_out = 0)
{
    $ping_error_fp = fopen("Ping_Error.txt", "a+");
    $recorderData['dateTime'] = date("Y-m-d H:i:s", time());
    $recorderStr = "------------------------------------------\r\n";
    if (is_array($recorderData)) {
        foreach ($recorderData as $k => $v) {
            if (!empty($v)) {
                $temp = @iconv("utf-8", "gb2312//ignore", $v);
                $recorderStr .= "{$k}:{$temp}\r\n";
            }
        }
    } else {
        if (!empty($recorderData)) {
            $temp = @iconv("utf-8", "gb2312//ignore", $recorderData);
            $recorderStr .= $temp . "\r\n";
        }
    }
    $str = <<<EOT
{$recorderStr}
EOT;
    fwrite($ping_error_fp, $str);
    fclose($ping_error_fp);
    if (!empty($is_out)) {
        exit();
    }
}

function sendCurlGet($url, $get_data = array())
{
    if (!empty($get_data)) {
        $get_data_str = http_build_query($get_data);
        $url = preg_match("/\?/", $url) ? $url . "&" . $get_data_str : $url . "?" . $get_data_str;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    $http_state = curl_getinfo($ch);
    curl_close($ch);
    return array($output, $http_state);
}

function http_build_query_noencode($queryArr)
{
    if (empty($queryArr)) {
        return "";
    }
    $returnArr = array();
    foreach ($queryArr as $key => $value) {
        $returnArr[] = $key . "=" . $value;
    }
    return implode("&", $returnArr);
}