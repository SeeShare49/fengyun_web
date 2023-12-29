<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/23
 * Time: 10:13
 */

namespace app\common;

use app\admin\model\ActivityType;
use app\admin\model\PropCsv;
use app\admin\model\TargetType;
use app\admin\model\Task;
use think\Controller;

class TypeManage extends Controller
{
    /**
     * 活动类型列表
     */
    public static function getActivityTypeList()
    {
        return ActivityType::field('id,name,sort,status')
            ->where('status', 1)
            ->select();
    }


    /**
     * 任务类型列表
     */
    public static function getTaskList()
    {
        return Task::field('id,name,sort,status')
            ->where('status', 1)
            ->select();
    }

    /**
     * 目标类型列表
     */
    public static function getTargetList()
    {
        return TargetType::field('id,name,sort,status')
            ->where('status', 1)
            ->select();
    }

    /**
     * 道具列表
     */
    public static function getPropList()
    {
        return PropCsv::field('type_id,sub_type,icon_id,type_name')->select();
    }
}