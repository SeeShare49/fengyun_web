<?php

namespace app\admin\controller;


use app\admin\model\ServerList;
use app\common\ServerManage;
use think\facade\View;

define('GROUP_ID', config('admin.GROUP_ID'));

class Furnace extends Base
{
    /**
     * 重生统计列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $server_id = trim(input('server_id'));
        $is_guild = false;
        $ids = '';
        $temp_server_ids = '';
        if (GROUPID == GROUP_ID) {
            $is_guild = true;
            $s_ids = get_user_server_list(UID);
            $temp_server_ids = '';
            foreach ($s_ids as $key => $value) {
                $temp_server_ids .= $value['server_id'] . ',';
            }
            $ids = explode(',', rtrim($temp_server_ids, ","));
            $server_list = ServerManage::getServerListByIds($ids);
        } else {
            $server_list = ServerManage::getServerList();
        }

        $where[] = ['use_status', '=', 1];
        if ($server_id) {
            $where[] = ['id', '=', $server_id];
        }

        if (empty($server_ids) && $is_guild == true) {
            $where[] = ['id', '=', rtrim($temp_server_ids, ",")];
        }

        $total_level_0 = 0;
        $total_level_1 = 0;
        $total_level_2 = 0;
        $total_level_3 = 0;
        $total_level_4 = 0;
        $total_level_5 = 0;
        $total_level_6 = 0;
        $total_level_7 = 0;
        $total_level_8 = 0;
        $total_level_9 = 0;
        $total_level_10 = 0;
        $total_level_11 = 0;
        $total_level_12 = 0;
        $total_level_13 = 0;
        $total_level_14 = 0;

        $data = array();
        $show_server = ServerList::where($where)->field('id')->select();
        foreach ($show_server as $s_value) {
            $info = dbConfigByReadBase($s_value['id'])
                ->table('furnace')
                ->field('count(case when `furnace_level`=0 then 1 end) as "level_0",
                     count(case when `furnace_level`=1 then 1 end) as "level_1",
                     count(case when `furnace_level`=2 then 1 end) as "level_2",
                     count(case when `furnace_level`=3 then 1 end) as "level_3",
                     count(case when `furnace_level`=4 then 1 end) as "level_4",
                     count(case when `furnace_level`=5 then 1 end) as "level_5",
                     count(case when `furnace_level`=6 then 1 end) as "level_6",
                     count(case when `furnace_level`=7 then 1 end) as "level_7",
                     count(case when `furnace_level`=8 then 1 end) as "level_8",
                     count(case when `furnace_level`=9 then 1 end) as "level_9",
                     count(case when `furnace_level`=10 then 1 end) as "level_10",
                     count(case when `furnace_level`=11 then 1 end) as "level_11",
                     count(case when `furnace_level`=12 then 1 end) as "level_12",
                     count(case when `furnace_level`=13 then 1 end) as "level_13",
                     count(case when `furnace_level`=14 then 1 end) as "level_14"')
                ->where('furnace_type', '=', 10)
                ->select();

            foreach ($info as $key => $value) {
                $level = new SummaryFurnaceLevelChart();
                $level->server_id = $s_value['id'];
                $level->level_0 = $value['level_0'];
                $total_level_0 += $value['level_0'];
                $level->level_1 = $value['level_1'];
                $total_level_1 += $value['level_1'];
                $level->level_2 = $value['level_2'];
                $total_level_2 += $value['level_2'];
                $level->level_3 = $value['level_3'];
                $total_level_3 += $value['level_3'];
                $level->level_4 = $value['level_4'];
                $total_level_4 += $value['level_4'];
                $level->level_5 = $value['level_5'];
                $total_level_5 += $value['level_5'];
                $level->level_6 = $value['level_6'];
                $total_level_6 += $value['level_6'];
                $level->level_7 = $value['level_7'];
                $total_level_7 += $value['level_7'];
                $level->level_8 = $value['level_8'];
                $total_level_8 += $value['level_8'];
                $level->level_9 = $value['level_9'];
                $total_level_9 += $value['level_9'];
                $level->level_10 = $value['level_10'];
                $total_level_10 += $value['level_10'];
                $level->level_11 = $value['level_11'];
                $total_level_11 += $value['level_11'];
                $level->level_12 = $value['level_12'];
                $total_level_12 += $value['level_12'];
                $level->level_13 = $value['level_13'];
                $total_level_13 += $value['level_13'];
                $level->level_14 = $value['level_14'];
                $total_level_14 += $value['level_14'];
                $data[] = $level;
            }
        }
        View::assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'lists' => objectToArray($data),
            'total_level_0' => $total_level_0,
            'total_level_1' => $total_level_1,
            'total_level_2' => $total_level_2,
            'total_level_3' => $total_level_3,
            'total_level_4' => $total_level_4,
            'total_level_5' => $total_level_5,
            'total_level_6' => $total_level_6,
            'total_level_7' => $total_level_7,
            'total_level_8' => $total_level_8,
            'total_level_9' => $total_level_9,
            'total_level_10' => $total_level_10,
            'total_level_11' => $total_level_11,
            'total_level_12' => $total_level_12,
            'total_level_13' => $total_level_13,
            'total_level_14' => $total_level_14,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '重生等级分布汇总'
        ]);
        return View::fetch();
    }


    public function lists()
    {
        $server_list = ServerManage::getServerList();
        $actor_id = trim(input('actor_id'));
        $server_id = trim(input('server_id'));
        $furnace_type = trim(input('furnace_type'));
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        $where[] = ['1', '=', 1];
        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        if ($furnace_type && $furnace_type != -1) {
            //特殊判断$furnace_type==0
            if ($furnace_type == 100) {
                $where[] = ['furnace_type', '=', 0];
            } else {
                $where[] = ['furnace_type', '=', $furnace_type];
            }
        }else{
            $furnace_type = 100;
            $where[] = ['furnace_type', '=', 0];
        }

        $lists = dbConfig($server_id)
            ->table('furnace')
            ->field('actor_id,furnace_type,furnace_level')
            ->where($where)
            ->order('furnace_level desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        View::assign([
            'server_id' => $server_id,
            'server_list' => $server_list,
            'actor_id' => $actor_id,
            'furnace_type' => $furnace_type,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '神炉-神翼-神兵-境界-重生'
        ]);
        return View::fetch();
    }
}

/**
 * 重生等级分布汇总
 **/
class SummaryFurnaceLevelChart
{
    public $server_id;
    public $level_0;
    public $level_1;
    public $level_2;
    public $level_3;
    public $level_4;
    public $level_5;
    public $level_6;
    public $level_7;
    public $level_8;
    public $level_9;
    public $level_10;
    public $level_11;
    public $level_12;
    public $level_13;
    public $level_14;
}

/**
 * 对象转数组
 * @param $object
 * @return array
 */
function objectToArray($object)
{
    $temp = is_object($object) ? get_object_vars($object) : $object;
    $arr = array();
    foreach ($temp as $k => $v) {
        $v = (is_array($v) || is_object($v)) ? objectToArray($v) : $v;
        $arr [$k] = $v;
    }
    return $arr;
}

