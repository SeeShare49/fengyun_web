<?php

namespace app\admin\controller;

use app\common\ServerManage;
use page\Page;
use think\facade\Log;

class QuestData extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        //$quest_list = \app\admin\model\GameQuestData::field('id,name')->select();
        $where[] = ['1', '=', 1];
        $quest_id = trim(input('quest_id'));
        if ($quest_id) {
            $where[] = ['quest_id', '=', $quest_id];
        }
        $server_id = trim(input('server_id'));
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        $classify = trim(input('classify'));
        if (isset($classify) && $classify != -1 && !empty($classify)) {
            $quest_ids = \app\admin\model\GameQuestData::where('type', 'like', "%$classify%")->field('id')->select();

            $ids = '';
            foreach ($quest_ids as $svr) {
                $ids .= $svr['id'].',';
            }
            $where[] = [['quest_id', 'in', trim($ids)]];
        }else{
            $classify==-1;
        }

        $player_count = dbConfig($server_id)->table('player')->count();

        $lists = dbConfig($server_id)->table('quest_data')
            ->field('quest_id,count(quest_id) as quest_count')
            ->where($where)
            ->group('quest_id')
            ->order('quest_id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'quest_id' => $quest_id,
            'server_id' => $server_id,
            'server_list' => $server_list,
            //'quest_list' => $quest_list,
            'classify'=>$classify,
            'player_count' => $player_count,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '任务统计信息列表'
        ]);
        return $this->fetch();
    }


    /**
     * 已完成的任务列表
     */
    public function completed()
    {
        $server_list = ServerManage::getServerList();
        $where[] = ['type', '=', '主线'];
        $quest_id = trim(input('quest_id'));
        if ($quest_id) {
            $where[] = ['id', '=', $quest_id];
        }
        $server_id = trim(input('server_id'));
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }
        $status = trim(input('status'));
        if ($status) {
            $sel_status = $status == 1 ? 1 : 0;
        } else {
            $sel_status = 1;
        }

        $player_count = dbConfig($server_id)->table('player')->count();
        $curr_page = input('page/d', 1);

        $quest_list_for = \app\admin\model\GameQuestData::field('id,name,type')->select();
        $quest_list = \app\admin\model\GameQuestData::where($where)->field('id,name,type')->select();

        $lists = array();
        foreach ($quest_list as $key => $value) {
            $temp_quest_id = $value['id'];
            $temp_quest_list = $this->quest_completed_lists($server_id, $temp_quest_id);
            foreach ($temp_quest_list as $tKey => $tValue) {
                $data['quest_id'] = $temp_quest_id;
                $data['status'] = $this->quest_is_completed($temp_quest_id, $tValue['completed_value']);
                $data['quest_name'] = $value['name'];
                $data['quest_type'] = $value['type'];
                $data['quest_num'] = 1;
                if ($data['status'] == $sel_status) {
                    array_push($lists, $data);
                }
            }
        }

        /** 合并同任务名称数组 **/
        $len = count($lists);
        for ($i = 0; $i < $len; $i++) {
            for ($j = $i + 1; $j < $len; $j++) {
                if ($lists[$i]['quest_name'] == $lists[$j]['quest_name'] && $lists[$i]['quest_id'] > 0) {
                    $lists[$i]['quest_num'] += $lists[$j]['quest_num'];
                    unset($lists[$j]);
                }
            }
        }

        $total = count($lists);
        $ret_lists = array_slice($lists, ($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS'));
        $pagernator = Page::make($lists, config('LIST_ROWS'), $curr_page, $total, false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
        $page = $pagernator->render();

        $this->assign([
            'server_list' => $server_list,
            'server_id' => $server_id,
            'player_count' => $player_count,
            'quest_id' => $quest_id,
            'quest_list' => $quest_list_for,
            'lists' => $ret_lists,
            'page' => $page,
            'status' => $status,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '用户获取已完成任务信息列表'
        ]);
        return $this->fetch();
    }

    /**
     * @param $server_id    服务器ID
     * @param $quest_id     配表任务ID
     * @return mixed
     * @throws \think\Exception
     */
    public function detail($server_id, $quest_id)
    {
        if (isset($server_id) && isset($quest_id)) {
            $lists = dbConfig($server_id)
                ->table('quest_data')
                ->field('actor_id,quest_id,receive_time')
                ->where('quest_id', '=', $quest_id)
                ->order('quest_id asc')
                ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
            $this->ifPageNoData($lists);
            $page = $lists->render();
            $this->assign([
                'lists' => $lists,
                'page' => $page,
                'quest_id' => $quest_id,
                'server_id' => $server_id,
                'empty' => '<td class="empty" colspan="5">暂无数据</td>',
                'meta_title' => '用户获取待完成任务信息列表'
            ]);
        }
        return $this->fetch();
    }

    /**
     * 根据服务器ID、任务ID获取已完成任务用户列表数据
     * @param $server_id    服务器ID
     * @param $quest_id     配表任务ID
     * @return
     * @throws \think\Exception
     */
    public function quest_completed_lists($server_id, $quest_id)
    {
        $completed_index = floor($quest_id / 32);
        return dbConfig($server_id)
            ->table('quest_completed')
            ->field('completed_index,completed_value')
            ->where('completed_index', '=', $completed_index)
            ->select();
    }

    /**
     * 校验任务完成状态
     * 0：待完成
     * 1：已完成
     * @param $quest_id     任务ID
     * @param $value        任务所在位置（二进制数据）
     * @return bool|int
     */
    public function quest_is_completed($quest_id, $value)
    {
        $nBitFlag = 0;
        $completed_value = $quest_id % 32;
        if ($completed_value < 32) {
            $nBitFlag = 1 << $completed_value;
            return ($value & $nBitFlag) != 0;
        }
        return $nBitFlag;
    }

    public function test_result()
    {
        $quest_id = 118;
        $completed_index = $quest_id / 32;
        $completed_value = $quest_id % 32;

        echo floor($completed_index);
        echo '<br/>';
        echo '<br/>';
        echo $completed_value;
        echo '<br/>';
        echo '<br/>';
        $nData = -470810624;

        $nBitFlag = 1 << $completed_value;
        echo ($nData & $nBitFlag) != 0;

        echo '<br/>';
        echo '<br/>';
        $array = array('aa', 'bb', 'aa', 3, 4, 5, 5, 5, 5, 'bc');
        dump($this->formatArray($array));

    }

    public function formatArray($array)
    {
        sort($array);
        $tem = "";
        $temarray = array();
        $j = 0;
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i] != $tem) {
                $temarray[$j] = $array[$i];
                $j++;
            }
            $tem = $array[$i];
        }
        return $temarray;
    }

    public function getGameQuestData($type)
    {
        if (isset($type) && $type != -1) {
            return \app\admin\model\GameQuestData::where('type', 'like', "%$type%")->field('id,name')->select();
        }
    }

}
