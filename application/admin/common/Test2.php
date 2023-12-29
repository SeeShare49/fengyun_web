<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 18:21
 */

//$selfPath = dirname(__FILE__);
//require($selfPath . "/SendSocketHelper.php");
//
//$bytes = new Bytes();
//
//$game_id = 56;
//$recharge_id = 190000000002;
//$strLen = strlen($recharge_id);
//$headType = 802;
//$headLength = 10 + $strLen;
//var_dump($headLength);
//$headLength = $bytes->shortToBytes(intval($headLength));
//$headType = $bytes->shortToBytes(intval($headType));
//$game_id = $bytes->integerToBytes(intval($game_id));
//$srecharge_id = strval($recharge_id);
//$strLen = $bytes->shortToBytes(strlen($srecharge_id));
//
//$recharge_id = $bytes->getBytes($srecharge_id);
//$return_betys = array_merge($headLength, $headType, $game_id, $strLen, $recharge_id);
////var_dump($return_betys);
//
//
//$msg = $bytes->toStr($return_betys);
//var_dump($msg);
//$obj = new SendSocketHelper();
//$obj->send($msg);


$test_array=array(1,5,6);

//echo "test array key:";
//echo  PHP_EOL;
//print_r(array_keys($test_array));
//$test_array_unique=array_values( array_unique($test_array));
//echo PHP_EOL;
//echo "Array result:";
//echo PHP_EOL;
//print_r($test_array_unique);
//for($i=0;$i<count($test_array_unique);$i++)
//{
//    echo 'key index:'.$i;
//    echo PHP_EOL;
//    echo 'value:'. $test_array_unique[$i];
//    echo PHP_EOL;
//}

function test($arr1,$arr2,$arr3)
{
    var_dump($arr1,$arr2,$arr3);
}

test($test_array[0],$test_array[1],$test_array[2]);
echo PHP_EOL;

test(...$test_array);