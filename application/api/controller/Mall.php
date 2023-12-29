<?php

namespace app\api\controller;

use think\facade\Log;
use X7\Client;
use X7\Constant\GameType;
use X7\Constant\ResponseCode;
use X7\Exception\ApiExceptionInterface;
use X7\Exception\BusinessException;
use X7\Exception\ServerRequestException;
use X7\Handler\ArrayParamHandler;
use X7\Module\X7mall\Constant\ApiMethod as X7mallApiMethod;
use X7\Module\X7mall\Model\IssuedProp;
use X7\Module\X7mall\Model\Prop;
use X7\Module\X7mall\Request\OrderNotifyRequest;
use X7\Module\X7mall\Request\PropIssueRequest;
use X7\Module\X7mall\Request\PropQueryRequest;
use X7\Module\X7mall\Request\RoleQueryRequest;
use X7\Module\X7mall\Response\OrderNotifyResponse;
use X7\Module\X7mall\Response\PropIssueResponse;
use X7\Module\X7mall\Response\PropQueryResponse;
use X7\Module\X7mall\Response\RoleQueryResponse;
use X7\Response\CommonResponse;
use X7\Utils\Json;

class Mall
{
    const RsaPrivateKey = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCHa8nO7dLAKs8W9hexcXJBrVKi1lHQOAinktN7AJWpGNAqM2IyDJ9jSzHoT//0eFsT2Hmt2KmNJtzEEPS0Js12PYNOkVbSW4lT8uTxTT65mbyApWj0BatKbgSI/HkSswKPbuwm4sr2lDyx0x7i/8BSlMWKnqAMv7WiokatJY+2cdNU1UWhr4AgKBkXk25WMN0+i59c3v4WjHdnUj+BkPip2bTrsEAlZbj/ZqztMsDo5wa5KteEDPP0PRE5Xrp0MVPfO/JeixdBE0LhId607ROk9aU76VNrj3m/jo3WN2kg8Dl2n8WRMmxIAeLMfAee8p2UyPBGWNEKpjxC0Mmt4ZEhAgMBAAECggEATTqkcZVhrmP7jE22P2TCU0BEM0dkiwmZre9nGevAbhoPhTGem4plT5wvjxwojUQiNNQsuH8qWqxCFLLSyvaiD/+OpbzvNbIsRCruc8qorfJF9Vvf2eJtyFmrzm+loItkN2Z72MVYGH17i7IqGCzggngDnEVQY1TlgiVnhxE3v0Sa2HgzcjgnNU8ni6eCK9fpSqx5q+rUkKSn/OtTojnIhIo5Q+4tPtV9+wKcnraKLemezwRPn+/7kI2eM48ZEGr7nEQw9yCST3dtHaquENxND1HS3KKuBq44xqS6OX+C00L90qbbA6mjr14+Tl2bAFWtRtfZwbpQDC7CBdZ3qJrq7QKBgQDqH7rxbpggsXgRFWgE7HGSztXqXOjeaVrK58/TudQmOmDmZPfuCCgdOl0QpjcVP3YMbxVe6i+idNV3A1W5DZIy/C+vtL4mC8TmmMZ8N9OP2+Xoqi4JXdITe7XvU5qKVEsNKaV3OxhW+V6SgqOXP+OfuuVeYyDZZ9Lsz6e4i92A6wKBgQCUExMAIf9pCf9c+JDzLnUYmGzAKy9nHRJ9lrojTUlHksanEnQefU6B6M0zTnYucW8xH0KOlFGx2k2adpp1B9L/4KGh9wunOf4yYjVYLS1dfFBKuHm6as8i9LeiBb+MDxtt1tG7WzNF90lqi5ZqA/EfpJsXWln6BCo8ZbYkhDaTIwKBgDlO7wmuEpreFg/Id5/M6LgQ3RfzCiV3EHxeZebjs+RvocyDppSjdn5BDQrIRO90i0bb4hVLqcFQa/gn3tHfWowUYu/VhD3334i6mMtLNwQdxZdAIOi2CHgwbgZZJpj5rS1ZpmEbHAgeHmIqjDS3tNYif4atKRtOaLkC04E+60dlAoGACAjQ6xztIuHr/TtvggJvUBpVLEgrlki5jB71kpzK8RtBtCcILe9Wpy3elH4ZY4O8KMotblcV57SUqWDuGJOIG2Iw4vzooPZAmLNDeblZ0MzB/ovr7vWEr7zfiecN8aeQMXGKh8P3EEDOtv1D+BSmrLTdxXKdlzdKo2RRccHKxEkCgYBx1PRHaDa6lu7z+mPRdmBgVH0QkN2Dzz46DenddfaSINOhEU2vPvyxT3hyuD2rF8vDflD7wybRJfre1BjZ6H2QTxXH18T+2X5Gq4MOd2P1E591ydhDjNXNc5NgHcoEaIy1Mif8748rtc84Scgy672mbqK83PFJ6nBGshk0NEgUHg==";       //平台私钥
    const RsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAh2vJzu3SwCrPFvYXsXFyQa1SotZR0DgIp5LTewCVqRjQKjNiMgyfY0sx6E//9HhbE9h5rdipjSbcxBD0tCbNdj2DTpFW0luJU/Lk8U0+uZm8gKVo9AWrSm4EiPx5ErMCj27sJuLK9pQ8sdMe4v/AUpTFip6gDL+1oqJGrSWPtnHTVNVFoa+AICgZF5NuVjDdPoufXN7+Fox3Z1I/gZD4qdm067BAJWW4/2as7TLA6OcGuSrXhAzz9D0ROV66dDFT3zvyXosXQRNC4SHetO0TpPWlO+lTa495v46N1jdpIPA5dp/FkTJsSAHizHwHnvKdlMjwRljRCqY8QtDJreGRIQIDAQAB";        //平台公钥
    const AndroidAppKey = "749dc322bbac9cbdf29fc65761b30d44";   //Android AppKey
    const IosAppKey = "499e2f1debbfd9d504522beb53a58f74";       //IOS AppKey
    protected $client;

    protected $requestUrl = "https://pay.x7sy.com/x7mall/gateway";

    protected $testRequestUrl = "https://pay.x7sy.com/x7mall_helper/gateway";


    public function test()
    {
        $apiMethod = "x7mall.propQuery";
        var_dump($apiMethod);
        $reqTime = date(DATE_ISO8601, time());
        $gameType = "client";
//        $bizParams = '{"roleId":12884912221,"guid":34827888}';
        $bizParams = '{"propCode":["10081"]}';
        $payload = Signature::genPayload($apiMethod, self::AndroidAppKey, $reqTime, $bizParams, $gameType);
        Log::write("生成的payload:" . $payload);
        $signature = Signature::sign($payload, self::RsaPrivateKey);
        Log::write("生成的签名:" . $signature);
        var_dump($bizParams);
        PHP_EOL;
        var_dump($reqTime);
        PHP_EOL;
        dump($signature);
        PHP_EOL;
    }

    public function test_convert()
    {
        $json_str = "{\"roleId\":\"12884912221\",\"guid\":\"34827888\"}";
        var_dump(json_decode($json_str, true));
        var_dump(urlencode($json_str));
    }
}