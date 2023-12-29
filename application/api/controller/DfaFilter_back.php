<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/10/16
 * Time: 17:46
 */

namespace app\api\controller;

use app\DfaFilter\SensitiveHelper;
use think\facade\Cache;
use think\facade\Log;

header("Access-Control-Allow-Origin: *");

class DfaFilter
{
    protected static $wordData;
    protected static $wordCache = array();

//    public function __construct()
//    {
//    }

    public static function getInstance()
    {
        if (isset(self::$wordData)) {
            return self::$wordData;
        } else {
            self::setFilterPool();
        }
    }

    public static function setFilterPool()
    {
        Log::write('execute again....');
        $file_name = '../public/csv/badword.txt';
        $wordPool = file_get_contents($file_name);
        self::$wordData = explode(',', $wordPool);
        cache('BAD_WORD_DATA', self::$wordData);
        //return self::$wordData;
    }

    /**
     * 获取过滤词汇内容
     * @param $word
     * @return array|string
     */
    public function getBadWord_back($word)
    {
        var_dump('get bad word:' . self::$wordCache);
//        $file_name = '../public/csv/badword.txt';
//        $wordPool = file_get_contents($file_name);
//        $this->wordData = explode(',', $wordPool);
        Log::write("coming get bad word....");
        Log::write("word:" . $word);
        if (isset($word)) {
//            SensitiveHelper::init()->setTree($this->wordData)->getBadWord($word);
            SensitiveHelper::init()->setTree(self::$wordCache)->getBadWord($word);
            $value_str = SensitiveHelper::init()->replace($word, '*', true);
            return json(['msgstr' => $value_str]);
        }
    }

    public function getBadWord($word)
    {
        $cache_data = Cache::get('BAD_WORD_DATA');
        if (!isset($cache_data)) {
            self::setFilterPool();
        } else {
            if (isset($word)) {
                SensitiveHelper::init()->setTree( Cache::get('BAD_WORD_DATA'))->getBadWord($word);
                $value_str = SensitiveHelper::init()->replace($word, '*', true);
                return json(['msgstr' => $value_str]);
            }
        }
    }
}