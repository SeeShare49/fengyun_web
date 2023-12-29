<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/23
 * Time: 10:13
 */

namespace app\common;

use app\admin\model\Channel;
use app\admin\model\GiftDataInfo;
use think\Controller;

class GiftDataInfoManage extends Controller
{
    /**
     * 礼物类型列表
     */
    public static function getGiftTypeList()
    {
        return  GiftDataInfo::field('id,gift_name')->group('gift_name')->order('id asc')->select();
    }
}