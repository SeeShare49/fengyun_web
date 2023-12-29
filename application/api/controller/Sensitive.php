<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/10/20
 * Time: 10:33
 */

namespace app\api\controller;


class Sensitive
{
    private static $instance = null;
    /**
     * 替换符号
     * @var string
     */
    private static $replaceSymbol = "*";
    /**
     * 敏感词树
     * @var array
     */
    private static $sensitiveWordTree = [];

    private function __construct()
    {
    }

    /**
     * 获取实例
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof Sensitive)) {
            return self::$instance = new self;
        }
        return self::$instance;
    }

    public static function addSensitiveWords($file_path)
    {
        foreach (self::readFile($file_path) as $words) {
            $len = mb_strlen($words);
            $treeArr = &self::$sensitiveWordTree;
            for ($i = 0; $i < $len; $i++) {
                $word = mb_substr($words, $i, 1);
                //敏感词树结尾记录状态为false；
                $treeArr = &$treeArr[$word] ? true : false;
            }
        }
    }

    /**
     * 执行过滤
     * @param string $txt
     * @return string
     */
    public static function execFilter($txt)
    {
        $wordList = self::searchWords($txt);
        if (empty($wordList))
            return $txt;
        return strtr($txt, $wordList);
    }

    /**
     * 搜索敏感词
     * @param string $txt
     * @return array
     */
    private static function searchWords($txt)
    {
        $txtLength = mb_strlen($txt);
        $wordList = [];
        for ($i = 0; $i < $txtLength; $i++) {
            //检查字符是否存在敏感词树内,传入检查文本、搜索开始位置、文本长度
            $len = self::checkWordTree($txt, $i, $txtLength);
            //存在敏感词，进行字符替换。
            if ($len > 0) {
                //搜索出来的敏感词
                $word = mb_substr($txt, $i, $len);
                $wordList[$word] = str_repeat(self::$replaceSymbol, $len);
            }
        }
        return $wordList;
    }

    /**
     * 检查敏感词树是否合法
     * @param string $txt 检查文本
     * @param int $index 搜索文本位置索引
     * @param int $txtLength 文本长度
     * @return int 返回不合法字符个数
     */
    private static function checkWordTree($txt, $index, $txtLength)
    {
        $treeArr = &self::$sensitiveWordTree;
        $wordLength = 0;//敏感字符个数
        $flag = false;
        for ($i = $index; $i < $txtLength; $i++) {
            $txtWord = mb_substr($txt, $i, 1); //截取需要检测的文本，和词库进行比对
            //如果搜索字不存在词库中直接停止循环。
            if (!isset($treeArr[$txtWord])) break;
            if ($treeArr[$txtWord] !== false) {//检测还未到底
                $treeArr = &$treeArr[$txtWord]; //继续搜索下一层tree
            } else {
                $flag = true;
            }
            $wordLength++;
        }
        //没有检测到敏感词，初始化字符长度
        $flag ?: $wordLength = 0;
        return $wordLength;
    }


    /**
     * 读取文件内容
     * @param string $file_path
     * @return Generator
     */
    private static function readFile(string $file_path)
    {
        $handle = fopen($file_path, 'r');
        while (!feof($handle)) {
            yield trim(fgets($handle));
        }
        fclose($handle);
    }

    private function __clone()
    {
        throw new \Exception("clone instance failed!");
    }

    private function __wakeup()
    {
        throw new \Exception("unserialize instance failed!");
    }
}