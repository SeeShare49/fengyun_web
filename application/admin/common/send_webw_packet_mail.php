<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 17:14
 */


/**
 * message mail_item
 * {
 * optional int32 item_id            = 1;
 * optional int32 item_count        = 2;
 * }
 * //804
 * message webw_packet_mail
 * {
 * optional int32    game_id            = 1;
 * optional string    player_name        = 2;
 * optional string    title            = 3;
 * optional string    content            = 4;
 * repeated mail_item items            = 5;
 * }
 **/


$selfPath = dirname(__FILE__);
require($selfPath . "/SendSocketHelper.php");
require($selfPath . "/PacketBase.php");

$bytes = new Bytes2();
$game_id = 898;
$title = "我是标题啦";
$title = iconv('UTF-8', 'GB2312', $title);
$player_name = "PHP好牛逼";
$player_name = iconv('UTF-8', 'GB2312', $player_name);
$content = "我是内容";
$content = iconv('UTF-8', 'GB2312', $content);

$items = array(
    array("item_id" => 1, "item_count" => 100),
    array("item_id" => 2, "item_count" => 200),
    array("item_id" => 3, "item_count" => 300),
);


$bytes->WriteInt($game_id);
$bytes->WriteString($title);
$bytes->WriteString($player_name);
$bytes->WriteString($content);
//$bytes->WriteString($items);
$bytes->WriteInt(3);
foreach ($items as $k => $v) {

    foreach ($items[$k] as $index => $value) {
        var_dump("index key:".$value);
        //echo $index . '<br>';
        //$bytes->WriteInt($index);
       // echo $value . '<br>';
        $bytes->WriteInt($value);
    }
}

$data = $bytes->GetData(804);
$msg = $bytes->toStr($data);
$obj = new SendSocketHelper();
$obj->send($msg);
