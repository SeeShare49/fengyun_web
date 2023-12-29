<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 13:32
 */

header("Content-type:text/html;charset=gbk");

/**
 * byte数组与字符串转化类
 * @author
 * Created on 2020/8/19
 */
class Bytes2
{
    public $len = 0;
    public $packet = array();

    public function shortToBytes($val)
    {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        return $byt;
    }

    /**
     * 转换一个shor字符串为byte数组
     * @param $byt 目标byte数组
     * @param $val 需要转换的字符串
     * @author Zikie
     */

    public function WriteShort($val)
    {
        global $packet;
        $packet[$this->len++] = ($val & 0xff);
        $packet[$this->len++] = ($val >> 8 & 0xff);
    }

    /**
     * 转换一个String字符串为byte数组
     * @param $str 需要转换的字符串
     * @param $bytes 目标byte数组
     * @author YW
     */

    function getBytes($str)
    {
        $len = strlen($str);
        $bytes = array();
        for ($i = 0; $i < $len; $i++) {
            if (ord($str[$i]) >= 128) {
                $byte = ord($str[$i]) - 256;
            } else {
                $byte = ord($str[$i]);
            }
            $bytes[] = $byte;
        }
        return $bytes;
    }

    /**
     * 转换一个int为byte数组
     * @param $byt 目标byte数组
     * @param $val 需要转换的字符串
     * @author Zikie
     */

    public function WriteInt($val)
    {
        global $packet;
        $packet[$this->len++] = ($val & 0xff);
        $packet[$this->len++] = ($val >> 8 & 0xff);
        $packet[$this->len++] = ($val >> 16 & 0xff);
        $packet[$this->len++] = ($val >> 24 & 0xff);
    }

    public function WriteString($val)
    {
        global $packet;
        $val_convert = strval($val);
        $strLen = strlen($val_convert);
        $this->WriteShort($strLen);
        $arrStr = $this->getBytes($val_convert); //self::getBytes($val_convert);
        $packet = array_merge($packet, $arrStr);
        $this->len = $strLen + $this->len;
    }

    public function GetData($code)
    {
        global $packet;
        $this->len = $this->len + 4;
        $len2 = $this->shortToBytes(intval($this->len));
        $code = $this->shortToBytes(intval($code));
        $packetData = array_merge($len2, $code, $packet);
        return $packetData;
    }

    public function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

}
