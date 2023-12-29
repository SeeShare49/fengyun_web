<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/10/16
 * Time: 16:54
 */

namespace app\DfaFilter\Exceptions;

use think\Exception;

class PdsBusinessException extends  Exception
{
    const EMPTY_CONTENT    = 10001;   // 空检测文本内容
    const EMPTY_WORD_POOL  = 10002;    // 空词库
    const CANNOT_FIND_FILE = 10003;    // 找不到词库文件
}