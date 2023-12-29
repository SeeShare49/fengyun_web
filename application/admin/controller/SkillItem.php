<?php

namespace app\admin\controller;


use app\admin\model\PropCsv;
use app\common\CsvManage;

class SkillItem extends Base
{
    public function index()
    {
        $skill_id = trim(input('skill_id'));
        if ($skill_id) {
            $where[] = ['skill_id', '=', $skill_id];
        }
        $skill_name = trim(input('skill_name'));
        $where[] = ['1', '=', 1];
        if ($skill_name) {
            $where[] = ['skill_name', 'like', "%$skill_name%"];
        }

        $lists = \app\admin\model\Skill::where($where)
            ->order('skill_id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'skill_name' => $skill_name,
            'lists' => $lists,
            'page' => $page,
            'skill_id' => $skill_id,
            'empty' => '<td class="empty" colspan="2">暂无数据</td>',
            'meta_title' => '技能信息列表'
        ]);
        return $this->fetch();
    }


    /**
     * 技能表上传
     **/
    public function upload()
    {
        $file_name = '../public/csv/skilldef.csv';
        $file_open = fopen($file_name, 'r');
        $count = 1;
        $items = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count >= 4) {
                for ($i = 0; $i < count($data); $i++) {
                    $item['skill_id'] = $data[0];
                    $item['skill_name'] = mb_convert_encoding($data[1], "UTF-8", "GBK");
                    array_push($items, $item);
                    //$item_name =  mb_convert_encoding($data[1], "UTF-8", "GBK");
                    break;
                }
            }
            $count++;
        }
        fclose($file_open);
        \app\admin\model\Skill::insertAll($items);
//        if (\app\admin\model\Skill::delete(true)) {
//            $result = \app\admin\model\Skill::insertAll($items);
////            if ($result) {
////                $this->success('技能数据添加成功!!!');
////
////            } else {
////                $this->error('清空技能表数据失败!!!');
////            }
//        }
    }
}
