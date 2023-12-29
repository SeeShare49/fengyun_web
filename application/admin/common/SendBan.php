<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 10:57
 */


$selfPath = dirname(__FILE__);
require($selfPath . "/SendSocketHelper.php");
require($selfPath . "/PacketBase.php");

/**
//账号id
optional int32	user_id			= 1;
//true,1为封号，false,0为解封
optional bool	ban				= 2;
//封号原因(仅备注)
optional string	reason			= 3;
 **/

$bytes = new Bytes2();
$user_id = 10000056;
$ban = 1;
$reason = "操蛋的PHP全局变量!";
$reason = iconv('UTF-8','GB2312',  $reason);
echo $reason;
$bytes->WriteString($user_id);
$bytes->WriteInt($ban);
$bytes->WriteString($reason);
$data = $bytes->GetData(803);
$msg = $bytes->toStr($data);
$obj = new SendSocketHelper();
$obj->send2($msg);
