<?php

namespace app\admin\controller;

use app\admin\model\PropCsv;
use app\common\CsvManage;
use think\Db;

class Skill extends Base
{
    /**
     * 显示技能资源列表
     * @param $id
     * @return mixed
     */
    public function index($id)
    {
        if ($id) {
            $where[] = ['actor_id', '=', $id];
            $lists = Db::connect('db_config1')->table('player_skill')
                ->order("skill_id desc")
                ->where($where)
                ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

            $this->ifPageNoData($lists);
            $page = $lists->render();

            $this->assign([
                'id' => $id,
                'lists' => $lists,
                'page' => $page,
                'meta_title' => '技能列表'
            ]);
            return $this->fetch();
        }
        $this->error('参数错误!');
    }
}
