<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/23
 * Time: 10:13
 */

namespace app\common;

use app\admin\model\Channel;
use think\Controller;

class ChannelManage extends Controller
{
    /**
     * 渠道类型列表
     */
    public static function getChannelList()
    {
        return Channel::select();
    }

    /**
     * 获取指定范围内的渠道列表
     * @param $ids
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChannelListByIds($ids)
    {
        $where[] = [
            ['id', 'in', $ids]
        ];
        return Channel::where($where)->select();
    }

    /**
     * 获取排除已选择的渠道列表
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChannelListExcludeId($id)
    {
        $where[] = [
            ['id', '<>', $id]
        ];
        return Channel::where($where)->select();
    }
}