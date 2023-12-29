<?php

/**
 * Class joloRsa
 * rsa加密工具类
 *
 * $account = '{"user_code":"104471097","user_name":"jolo666","game_code":"3143016315883","session_id":"12842658","id":"1573633923000"}';
 * $account_sign = '13DersLTir22ofrzbxii4+N44gByUIZi6vg/ulP8ru9vid0GOY3sVvgSoAmBkNezKiUWEqei/gPrPERYUccbpM05yh1I4Bs81nunLx1GrlDl6FU3nPRzC4a8mtDbCcU3aZ+lzSXk/8ieymPXdrOMtq6ncmqgVf/jk2A8K151oXQ=';
 */
class joloRsa
{
    /**
     *  $orderArr = [];
     *       $orderArr['game_name']     = 'game_name';  //游戏名称
     *       $orderArr['game_code']     = 'game_code';  //游戏编号(gamecode)
     *       $orderArr['game_order_id'] = 'game_order_id';//游戏订单id
     *       $orderArr['product_id']    = 'product_id';//商品ID
     *       $orderArr['product_name']  = 'product_name';//商品名称
     *       $orderArr['product_des']   = 'product_des';//商品描述
     *       $orderArr['amount']        = 'amount';//金额,单位为分
     *       $orderArr['notify_url']    = 'notify_url';//支付回调地址
     *       $orderArr['user_code']     = 'user_code';//用户编号
     *       $orderArr['session_id']    = 'session_id';
     * HTC订单签名
     */
	// 注意：参数里，不要出现类似“1元=10000个金币”的字段，因为“=”原因，会导致微信支付校验失败
	// 参数值不能出现json串，会导致支付宝扫码支付失败
	// product_id 为字符串类型
    function order_rsa_sign($orderArr)
    {
        if (!is_array($orderArr)) {
            return null;
        }
        $priPKSC8 = '替换PKSC8密钥';
        $pem = chunk_split($priPKSC8, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        $res = openssl_get_privatekey($pem);
        if (!$res) {
            return false;
        }
		//配置参数 JSON_UNESCAPED_UNICODE $orderArr转JSON格式时中文不转义
        openssl_sign(json_encode($orderArr,JSON_UNESCAPED_UNICODE), $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    function verification_sign($input, $sign, $pub)
    {
        if (empty($input) || empty($sign) || empty($pub)) {
            return false;
        }
        $pub_key = $this->setupPubKey($pub);
        $res = openssl_get_publickey($pub_key);
        $result = openssl_verify($input, base64_decode($sign), $res);
        if ($res) {
            openssl_free_key($res);
        }
        return $result == 1 ? true : false;

    }

     function setupPubKey($pubKey)
    {
        if (is_resource($pubKey)) {
            return true;
        }
        $pem = chunk_split($pubKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $pub_Key = openssl_pkey_get_public($pem);
        return $pub_Key;
    }

   function  rsa_verify($data, $signature, $public_key) {
        if(empty($data) || empty($signature)) {
            return false;
        }
        $pub_res = openssl_get_publickey($public_key);
        return openssl_verify($data, base64_decode($signature), $pub_res );
    }

}

