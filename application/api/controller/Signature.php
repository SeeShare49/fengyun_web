<?php

namespace app\api\controller;

class Signature
{
    /**
     * 签名
     *
     * @param string $payload
     * @param string $rsaPrivateKey
     * @param int $algo
     * @return string
     */
    public static function sign($payload, $rsaPrivateKey, $algo = OPENSSL_ALGO_SHA256)
    {
        $formatPrivateKey = self::formatRsaPrivateKey($rsaPrivateKey);
        openssl_sign($payload, $signature, $formatPrivateKey, $algo);
        return base64_encode($signature);
    }


    /**
     * 生成payload
     *
     * @param string $apiMethod
     * @param string $appkey
     * @param string $datetime
     * @param string $body
     * @param string $gameType
     * @param string $method
     * @return string
     */
    public static function genPayload($apiMethod, $appkey, $datetime, $body, $gameType, $method = "POST")
    {
        $payload = $method . " " . $apiMethod . "@" . $appkey . "#" . $gameType . "."
            . $datetime . "\n\n" . $body;
        return $payload;
    }

    /**
     * 验签
     *
     * @param string $payload
     * @param string $signature
     * @param string $rsaPublicKey
     * @param int $algo
     * @return int
     */
    public static function verify($payload, $signature, $rsaPublicKey, $algo = OPENSSL_ALGO_SHA256)
    {
        $rawSignature = base64_decode($signature);
        $formatPublicKey = self::formatRsaPublicKey($rsaPublicKey);
        return openssl_verify($payload, $rawSignature, $formatPublicKey, $algo);
    }


    /**
     * 格式化私钥
     *
     * @param string $privateKey
     * @return string
     */
    public static function formatRsaPrivateKey($privateKey)
    {
        return "-----BEGIN RSA PRIVATE KEY-----\r\n" . wordwrap($privateKey, 64, "\r\n", TRUE) . "\r\n-----END RSA PRIVATE KEY-----";
    }

    /**
     * 格式化公钥
     *
     * @param string $publicKey
     * @return string
     */
    public static function formatRsaPublicKey($publicKey)
    {
        return "-----BEGIN PUBLIC KEY-----\r\n" . wordwrap($publicKey, 64, "\r\n", TRUE) . "\r\n-----END PUBLIC KEY-----";
    }
}