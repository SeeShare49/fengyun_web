<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 17:08
 */


/**
 * 801

optional int32 game_id			= 1;
optional string player_name		= 2;
//元宝
optional int32 yuanbao			= 3;
//金币
optional int32 gold				= 4;
//钻石
optional int32 diamonds			= 5;
 **/


$selfPath = dirname(__FILE__);
require($selfPath . "/SendSocketHelper.php");
require($selfPath . "/PacketBase.php");


$bytes = new Bytes2();
$game_id = 123;
$player_name = "风吹裤裆爽";
$yuanbao = 10000;
$gold = 898521;
$diamonds = 1320000;
$player_name = iconv('UTF-8','GB2312',  $player_name);

$bytes->WriteInt($game_id);
$bytes->WriteString($player_name);
$bytes->WriteInt($yuanbao);
$bytes->WriteInt($gold);
$bytes->WriteInt($diamonds);
$data = $bytes->GetData(801);
$msg = $bytes->toStr($data);
$obj = new SendSocketHelper();
$obj->send($msg);
