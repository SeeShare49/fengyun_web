<?php

namespace app\api\controller;

use think\Controller;
use think\facade\Db;
use think\facade\Log;

/**
 * 游戏公告接口
 */
class GameNotice
{
    /**
     *
     * @param $channelid
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $channelid = $_GET['channelid'];
        if (isset($channelid)) {
            if (intval($channelid >= 100)) {
                $lists = \app\api\model\GameNotice::where(
                    [
                        ['status', '=', 1]
                    ]
                )
                    ->field('id,title,content,is_picture,image_url,link_url,start_time,end_time,image_width,image_height')
                    ->order('is_top desc,id asc,create_time desc')->select()->toJson();
            } else {
                $lists = \app\admin\model\GameNotice::where(
                    [
                        ['status', '=', 1]
                    ]
                )
                    ->field('id,title,content,is_picture,image_url,link_url,start_time,end_time,image_width,image_height')
                    ->order('is_top desc,id asc,create_time desc')->select()->toJson();
            }
        } else {
            $lists = \app\admin\model\GameNotice::where(
                [
                    ['status', '=', 1]
                ]
            )
                ->field('id,title,content,is_picture,image_url,link_url,start_time,end_time,image_width,image_height')
                ->order('is_top desc,id asc,create_time desc')->select()->toJson();
        }
        $json = "{\"code\": 0,\"msg\": \"游戏公告数据\",\"data\": ";
        Log::write("游戏公告发送json字符串:" . $json . stripslashes($lists) . "}");
        return $json . stripslashes($lists) . "}";
    }
}
