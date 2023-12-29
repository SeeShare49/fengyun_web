<?php
/*************************
 * The Author Of ALone
 **************************/
include "common.php";
PingErrorRecorder($_POST + array("input_string" => file_get_contents("php://input")));
/************************************************************************************************************************************************************************************************************************************************************************************************************************************
 * 注意，实例代码只是提供一个参考作用，游戏开放商不应该直接性使用实例代码应该结合自身项目中代码风格或者项目框架代码风格使用，结合自身代码的安全性来使用代码。
 * 支付回调参数：
 * encryp_data                    将game_orderid（游戏订单号）、guid（用户唯一标识）、pay_price（道具金额）使用私钥加密起来的，游戏厂商需要在验证sign_data后将当前参数使用公钥解密对比【游戏订单号】、【用户唯一标识】、【道具金额】。
 * extends_info_data        当前参数是小7提供的支付透传参数
 * game_area                        角色所在的游戏区
 * game_level                    用户游戏角色等级
 * game_orderid                游戏订单号
 * game_role_id                游戏角色ID信息
 * game_role_name            游戏角色名称
 * sdk_version                    使用当前回调的SDK版本
 * subject                            游戏道具名称
 * xiao7_goid                    游戏订单在小7的唯一标识
 * sign_data                        是对上面所有字段的私钥签名
 * --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * 对于发送支付回调小7服务器默认是通过发送POST请求的方式类型为：application/x-www-form-urlencoded （在没有特殊说明下都是使用POST请求）
 * 在POST方式下如果游戏厂商使用输入/输出流的方式获取POST数据，如PHP的file_get_contents("php://input")，那么获取到的数据就像下面的一连串的QueryString字符串，并且进行编码过的：
 * encryp_data=NtPZfezR7l2cSq2%2BI2MYhODxtxFog6LEKayZuz2ssl5wIotdjnhUucQYjvytqogOiXvN6SbPw6BZCScxgqgyR0hNX0d6r2XLpAbsK9P0thuoyWhQusk%2FQiWvAQ3hmsADZ11F9GYRBTacaLRITW8gKxzUhjB73x4BrGhLjOhvGbY%3D&extends_info_data=%E6%89%A9%E5%B1%95%E5%8F%82%E6%95%B0&game_area=11&game_level=1&game_orderid=2018182571972272&game_role_id=%E6%89%80%E4%BB%A5%E5%8C%BAID&game_role_name=%E6%89%80%E4%BB%A5%E5%8C%BA%E5%90%8D%E7%A7%B0&sdk_version=2.0&subject=%E5%95%86%E5%93%81%E6%8F%8F%E8%BF%B0&xiao7_goid=2093061&sign_data=iR2PybCYT1E%2F1iU7gAvhTzpVQM9cEJwOy84XxEDVgg4L75jr1b6fZhlDuGiYG%2FM%2BoWBlRUAecEl3mpzfQ%2Fh%2FsnNMa9bGCDwzRNKsrlinAzo4kybV7PBqxCbePT1wNo%2FE3Pa%2FCaywCYB2Qe0y96Q7lhaRd955uQpx4eg2qFnXDgY%3D
 * 如果游戏厂商使用如PHP的$_POST和$_REQUEST等方式获取数据，那么将会是一个键值对数组并且是没有编码过的（具体是否有没有编码过与服务器PHP中的PHP.INI文件设置有关）。
 * 然而无论接收到的数据是编码过还是没有编码过，游戏厂商在继续执行下面操作之前需要保证执行下面操作时候所有参数都是没有经过编码的数据。也就是如果游戏厂商接收到被编码过的数据那么就需要进行一次反编码操作才能继续下面执行。
 **************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/****************************************************************************************************************************************************************************************************************************
 * 下面这里提供一组测试数据（正式处理的时候需要将公钥替换成小7分发给游戏端的专用公钥）：
 * 公钥：
 * MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+zCNgOlIjhbsEhrGN7De2uYcfpwNmmbS6HYYI5KljuYNua4v7ZsQx5gTnJCZ+aaBqAIRxM+5glXeBHIwJTKLRvCxC6aD5Mz5cbbvIOrEghyozjNbM6G718DvyxD5+vQ5c0df6IbJHIZ+AezHPdiOJJjC+tfMF3HdX+Ng/VT80LwIDAQAB
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * 使用$_POST和$_REQUEST等方式获取的数据：
 * encryp_data:NtPZfezR7l2cSq2+I2MYhODxtxFog6LEKayZuz2ssl5wIotdjnhUucQYjvytqogOiXvN6SbPw6BZCScxgqgyR0hNX0d6r2XLpAbsK9P0thuoyWhQusk/QiWvAQ3hmsADZ11F9GYRBTacaLRITW8gKxzUhjB73x4BrGhLjOhvGbY=
 * extends_info_data:扩展参数
 * game_area:11
 * game_level:1
 * game_orderid:2018182571972272
 * game_role_id:所以区ID
 * game_role_name:所以区名称
 * sdk_version:2.0
 * subject:商品描述
 * xiao7_goid:2093061
 * sign_data:iR2PybCYT1E/1iU7gAvhTzpVQM9cEJwOy84XxEDVgg4L75jr1b6fZhlDuGiYG/M+oWBlRUAecEl3mpzfQ/h/snNMa9bGCDwzRNKsrlinAzo4kybV7PBqxCbePT1wNo/E3Pa/CaywCYB2Qe0y96Q7lhaRd955uQpx4eg2qFnXDgY=
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * 使用file_get_contents("php://input")获取的数据：
 * encryp_data=NtPZfezR7l2cSq2%2BI2MYhODxtxFog6LEKayZuz2ssl5wIotdjnhUucQYjvytqogOiXvN6SbPw6BZCScxgqgyR0hNX0d6r2XLpAbsK9P0thuoyWhQusk%2FQiWvAQ3hmsADZ11F9GYRBTacaLRITW8gKxzUhjB73x4BrGhLjOhvGbY%3D&extends_info_data=%E6%89%A9%E5%B1%95%E5%8F%82%E6%95%B0&game_area=11&game_level=1&game_orderid=2018182571972272&game_role_id=%E6%89%80%E4%BB%A5%E5%8C%BAID&game_role_name=%E6%89%80%E4%BB%A5%E5%8C%BA%E5%90%8D%E7%A7%B0&sdk_version=2.0&subject=%E5%95%86%E5%93%81%E6%8F%8F%E8%BF%B0&xiao7_goid=2093061&sign_data=iR2PybCYT1E%2F1iU7gAvhTzpVQM9cEJwOy84XxEDVgg4L75jr1b6fZhlDuGiYG%2FM%2BoWBlRUAecEl3mpzfQ%2Fh%2FsnNMa9bGCDwzRNKsrlinAzo4kybV7PBqxCbePT1wNo%2FE3Pa%2FCaywCYB2Qe0y96Q7lhaRd955uQpx4eg2qFnXDgY%3D
 *****************************************************************************************************************************************************************************************************************************/
$public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+zCNgOlIjhbsEhrGN7De2uYcfpwNmmbS6HYYI5KljuYNua4v7ZsQx5gTnJCZ+aaBqAIRxM+5glXeBHIwJTKLRvCxC6aD5Mz5cbbvIOrEghyozjNbM6G718DvyxD5+vQ5c0df6IbJHIZ+AezHPdiOJJjC+tfMF3HdX+Ng/VT80LwIDAQAB";

define("PUBLIC_KEY", $public_key);
/****************************************************************************************************************************************************************************************************************************
 * 这里是假设使用PHP的file_get_contents("php://input")方式获取到数据。获取到的数据格式是querystring形式并且数据是被编码过的，如：
 * encryp_data=NtPZfezR7l2cSq2%2BI2MYhODxtxFog6LEKayZuz2ssl5wIotdjnhUucQYjvytqogOiXvN6SbPw6BZCScxgqgyR0hNX0d6r2XLpAbsK9P0thuoyWhQusk%2FQiWvAQ3hmsADZ11F9GYRBTacaLRITW8gKxzUhjB73x4BrGhLjOhvGbY%3D&extends_info_data=%E6%89%A9%E5%B1%95%E5%8F%82%E6%95%B0&game_area=11&game_level=1&game_orderid=2018182571972272&game_role_id=%E6%89%80%E4%BB%A5%E5%8C%BAID&game_role_name=%E6%89%80%E4%BB%A5%E5%8C%BA%E5%90%8D%E7%A7%B0&sdk_version=2.0&subject=%E5%95%86%E5%93%81%E6%8F%8F%E8%BF%B0&xiao7_goid=2093061&sign_data=iR2PybCYT1E%2F1iU7gAvhTzpVQM9cEJwOy84XxEDVgg4L75jr1b6fZhlDuGiYG%2FM%2BoWBlRUAecEl3mpzfQ%2Fh%2FsnNMa9bGCDwzRNKsrlinAzo4kybV7PBqxCbePT1wNo%2FE3Pa%2FCaywCYB2Qe0y96Q7lhaRd955uQpx4eg2qFnXDgY%3D
 * 需要将这种数据转换成键值对数组的形式，接着键值对反编码。如果是使用如PHP的$_POST和$_REQUEST等方式获取数据并且数据是没有被编码过的话，那么可以忽略当前步骤。
 *****************************************************************************************************************************************************************************************************************************/
$request_string = "extends_info_data=%E6%89%A9%E5%B1%95%E5%8F%82%E6%95%B0&game_area=11&game_level=1&game_orderid=2018182571972272&game_role_id=%E6%89%80%E4%BB%A5%E5%8C%BAID&game_role_name=%E6%89%80%E4%BB%A5%E5%8C%BA%E5%90%8D%E7%A7%B0&sdk_version=2.0&subject=%E5%95%86%E5%93%81%E6%8F%8F%E8%BF%B0&xiao7_goid=2093061&sign_data=iR2PybCYT1E%2F1iU7gAvhTzpVQM9cEJwOy84XxEDVgg4L75jr1b6fZhlDuGiYG%2FM%2BoWBlRUAecEl3mpzfQ%2Fh%2FsnNMa9bGCDwzRNKsrlinAzo4kybV7PBqxCbePT1wNo%2FE3Pa%2FCaywCYB2Qe0y96Q7lhaRd955uQpx4eg2qFnXDgY%3D&encryp_data=NtPZfezR7l2cSq2%2BI2MYhODxtxFog6LEKayZuz2ssl5wIotdjnhUucQYjvytqogOiXvN6SbPw6BZCScxgqgyR0hNX0d6r2XLpAbsK9P0thuoyWhQusk%2FQiWvAQ3hmsADZ11F9GYRBTacaLRITW8gKxzUhjB73x4BrGhLjOhvGbY%3D";

parse_str($request_string, $post_data);
/************************************
 * 这里的对sign_data解64编码
 ************************************/
$post_sign_data = base64_decode($post_data["sign_data"]);
/************************************
 * 因为sign_data是不加入签名里面的
 ************************************/
unset($post_data["sign_data"]);
//按照参数名称的正序排序
ksort($post_data);
//对输入参数根据参数名排序，并拼接为key=value&key=value格式；
$sourcestr = http_build_query_noencode($post_data);
//对数据进行验签，注意对公钥做格式转换
$publicKey = ConvertPublicKey(PUBLIC_KEY);
$verify = Verify($sourcestr, $post_sign_data, $publicKey);
//判断签名是否是正确
if ($verify != 1) {
    ReturnResult('sign_data_verify_failed');
}
//对加密的encryp_data进行解密
$post_encryp_data_decode = base64_decode($post_data["encryp_data"]);
$decode_encryp_data = PublickeyDecodeing($post_encryp_data_decode, $publicKey);
parse_str($decode_encryp_data, $encryp_data_arr);
if (!isset($encryp_data_arr["pay_price"]) || !isset($encryp_data_arr["guid"]) || !isset($encryp_data_arr["game_orderid"])) {
    ReturnResult('encryp_data_decrypt_failed');
}
/************************************************************************************
 * 这时候得到的$encryp_data_arr数组内容包含game_orderid、guid、pay_price 三个内容。
 * 下面我们通过游戏订单号在数据库查找到下面内容：
 *************************************************************************************/
$arr = array(
    "game_area" => "11",
    "game_orderid" => "2018182571972272",
    "game_role_id" => "所以区ID",
    "game_role_name" => "所以区名称",
    "guid" => 1219663,
    "xiao7_goid" => 2093061,
    "pay_price" => "1.00"
);
$needCompareData = array(
    "game_area" => "game_area error",
    "game_orderid" => "game_orderid error",
    "game_role_id" => "game_role_id error",
    "game_role_name" => "game_role_name error",
    "guid" => "guid error",
    "xiao7_goid" => "xiao7_goid error",
    "pay_price" => "pay_price error"
);
if (!isset($encryp_data_arr['game_orderid']) || $encryp_data_arr['game_orderid'] != $post_data['game_orderid']) {
    ReturnResult("failed:" . $needCompareData["game_orderid"]);
}
$post_data += $encryp_data_arr;
foreach ($needCompareData as $key => $value) {
    if ($key == "pay_price") {
        if (bccomp($post_data[$key], $arr[$key], 2) != 0) {
            ReturnResult("failed:" . $value);
        }
    } else if ($arr[$key] != $post_data[$key]) {
        ReturnResult("failed:" . $value);
    }
}
ReturnResult("success");








