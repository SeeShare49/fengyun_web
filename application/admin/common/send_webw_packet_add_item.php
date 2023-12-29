<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 17:11
 */


/**
 *
 * //800
message webw_packet_add_item
{
optional int32 game_id			= 1;
optional string player_name		= 2;
optional int32 item_id			= 3;
optional int32 item_count		= 4;
optional int32 bind				= 5;
}
**/


$selfPath = dirname(__FILE__);
require($selfPath . "/SendSocketHelper.php");
require($selfPath . "/PacketBase.php");


$bytes = new Bytes2();
$game_id = 1111;
$player_name = "PHP好牛逼";
$item_id =999;
$item_count =999;
$bind =0;
$player_name = iconv('UTF-8','GB2312',  $player_name);




$bytes->WriteInt($game_id);
$bytes->WriteString($player_name);
$bytes->WriteInt($item_id);
$bytes->WriteInt($item_count);
$bytes->WriteInt($bind);
$data = $bytes->GetData(800);
$msg = $bytes->toStr($data);
$obj = new SendSocketHelper();
$obj->send($msg);
