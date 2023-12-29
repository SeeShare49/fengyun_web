<?php


namespace app\common;


use app\admin\model\GameLogAction;
use app\admin\model\ServerList;
use think\Controller;

class GameLogActionManage extends Controller
{
    /***
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 消耗元宝场景
     */
    public static function getCostSceneList()
    {
        return GameLogAction::name('game_log_action')->where('scene', 'in', '1,3')->select();
    }

    /**
     * 增加元宝场景
    */
    public static function getAddSceneList()
    {
        return GameLogAction::name('game_log_action')->where('scene','in', '2,3')->select();
    }

    /**
     * 使用场景
    */
    public static  function  getPropUseScene()
    {
        //return GameLogAction::name('game_log_action')->where('scene','in','1,2,3')->select();
        return GameLogAction::name('game_log_action')->select();

    }
}