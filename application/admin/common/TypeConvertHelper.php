<?php


namespace app\admin\common;

/**
 * 类型转换帮助类
 */
class TypeConvertHelper
{
    /**
     * 数组转字符串
     * @param $arr
     * @return string
     */
    public static function ArrayToStr($arr)
    {
        $str = "";
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $str .= urlencode($key) . "[]=" . urlencode($item) . "&";
                }
            } else {
                $str .= urlencode($key) . "=" . urlencode($value) . "&";
            }
        }
        return $str;
    }

    /**
     * 字符串转数组
     * @param $str
     * @return array
     */
    public static function StrToArray($str)
    {
        $arr = array();
        if (!empty($str)) {
            parse_str($str, $arr);
        }
        return $arr;
    }
}