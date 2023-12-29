<?php


namespace app\common;

use think\Controller;


/**
 * 游戏日志行为类型
 */
class GameLogActionType extends Controller
{
    public static function getActionTypeList()
    {
        return \app\admin\model\GameLogActionType::name('game_log_action_type')->where('status', '=', 1)->select();
    }

    public static function GetActionTypeListByIds()
    {
        return \app\admin\model\GameLogActionType::name('game_log_action_type')
            ->field('action_type_value,action_type_desc')
            ->where([
                ['status', '=', 1],
                ['action_type_value', 'in', [2, 3, 8, 9]]
            ])->select();
    }
}