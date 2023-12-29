<?php

namespace app\admin\controller;

use app\admin\model\Notice as NoticeModel;
use app\admin\validate\Notice as NoticeValidate;
use app\common\ServerManage;
use app\common\test;
use think\facade\View;

class ReloadTable extends Base
{
    public function index()
    {
        $where[] = ['1', '=', 1];

        $lists = \app\admin\model\ReloadTable::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="4">暂无数据</td>',
            'meta_title' => '更新加载CSV数据表'
        ]);
        return View::fetch();
    }

    /**
     * 新增更新加载CSV数据表
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $data['operator'] = USERNAME;
            $data['create_time'] = time();
            $re = \app\admin\model\ReloadTable::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("reload_table_add", "reload_table", $re, UID);
                test::webw_packet_reload_table($data['server_ids']);
                $this->success('CSV数据表文件更新成功!', 'reload_table/index');
            } else {
                $this->error("CSV数据表文件更新失败!");
            }
        } else {
            //初始化服务器列表
            $server_list = ServerManage::getServerList();
            $this->assign([
                'server_list' => $server_list,
                'meta_title' => '更新加载CSV数据表'
            ]);
            return $this->fetch();
        }
    }
}
