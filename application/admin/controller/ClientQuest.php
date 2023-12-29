<?php

namespace app\admin\controller;


use app\admin\model\ServerList;
use app\common\ServerManage;
use app\common\test;
use tests\webw_packet_add_quest;
use think\facade\Request;

class ClientQuest extends Base
{
    /**
     * 任务列表
     * 客户端与策划使用
     */
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $nickname = trim(input('nickname'));
        $where[] = ['1', '=', 1];

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        if ($nickname) {
            $where[] = ['nickname', 'like', "%$nickname%"];
        }
        $lists = \app\admin\model\ClientQuest::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'nickname' => $nickname,
            'server_id' => $server_id,
            'server_list'=>$server_list,
            'meta_title' => '任务配置(客户端与策划专用)列表'
        ]);
        return $this->fetch();
    }

    /**
     * 新增配置任务
    **/
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $questValidate = new \app\admin\validate\ClientQuest();
            if (!$questValidate->check($data)) {
                $this->error($questValidate->getError());
            }

            $data['create_time'] = time();
            $re = \app\admin\model\ClientQuest::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("client_quest_add", "client_quest", $re, UID);
                test::webw_packet_add_quest($data['server_id'], $data['nickname'], $data['quest_id']);
                $this->success('客户端任务配置成功', 'ClientQuest/index');
            } else {
                $this->error('客户端任务配置失败');
            }
        } else {
            $server_list = ServerManage::getServerList();
            $this->assign(
                [
                    'meta_title' => '客户端添加任务配置',
                    'server_list' => $server_list
                ]
            );
            return $this->fetch();
        }
    }
}
