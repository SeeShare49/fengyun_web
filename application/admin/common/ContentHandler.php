<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 16:07
 */



/**
 * 包体类
 * 包含了对包体的操作
 */
class ContentHandler
{
    public $buf;
    public $pos;
    public $contentlen;//use for unpack

    function __construct()
    {
        $this->buf = "";
        $this->contentlen = 0;
        $this->pos = 0;
    }

    function __destruct()
    {
        unset($this->buf);
    }

    public function PutInt($int)
    {
        $this->buf .= pack("i", (int)$int);
    }

    public function PutUTF($str)
    {
        $l = strlen($str);
        $this->buf .= pack("s", $l);
        $this->buf .= $str;
    }

    public function PutStr($str)
    {
        return $this->PutUTF($str);
    }


    public function TellPut()
    {
        return strlen($this->buf);
    }

    /*******************************************/

    public function GetInt()
    {
        //$cont = substr($out,$l,4);
        $get = unpack("@" . $this->pos . "/i", $this->buf);
        if (is_int($get[1])) {
            $this->pos += 4;
            return $get[1];
        }
        return 0;
    }

    public function GetShort()
    {
        $get = unpack("@" . $this->pos . "/S", $this->buf);
        if (is_int($get[1])) {
            $this->pos += 2;
            return $get[1];
        }
        return 0;
    }

    public function GetUTF()
    {
        $getStrLen = $this->GetShort();

        if ($getStrLen > 0) {
            $end = substr($this->buf, $this->pos, $getStrLen);
            $this->pos += $getStrLen;
            return $end;
        }
        return '';
    }

    /***************************/

    public function GetBuf()
    {
        return $this->buf;
    }

    public function SetBuf($strBuf)
    {
        $this->buf = $strBuf;
    }

    public function ResetBuf()
    {
        $this->buf = "";
        $this->contentlen = 0;
        $this->pos = 0;
    }
}