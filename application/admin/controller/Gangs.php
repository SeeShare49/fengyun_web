<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\common\test;
use Config;
use Session;
use app\common\ServerManage;
use tests\webw_packet_change_sect_master;
use think\facade\Request;
use think\facade\View;

class Gangs extends Base
{
    /**
     * 帮派列表
     */
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $where[] = ['1', '=', '1'];
        $guild_name = trim(input('guildname'));
        if ($guild_name) {
            $where[] = ['s.sect_name', 'like', "%$guild_name%"];
        }

        $server_id = trim(input('server_id'));
        if (empty($server_id) || $server_id == "0") {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }

        $serverInfo = ServerList::find($server_id);
        if ($serverInfo) {
            Session::set("server_id", $serverInfo['real_server_id']);
            $lists = dbConfig($serverInfo['real_server_id'])
                ->table('sect')
                ->alias('s')
                ->join('sect_member sm', 's.sect_id = sm.sect_id')
                ->join('player p', 'sm.actor_id = p.actor_id', 'LEFT')
                ->where($where)
                ->where('sm.title', '=', 100)
                ->field('s.sect_id,s.sect_name,s.level,p.nickname as nickname,s.notice')
                ->order('s.sect_id desc')
                ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

            $this->ifPageNoData($lists);
            $page = $lists->render();

            $this->assign([
                'guildname' => $guild_name,
                'lists' => $lists,
                'page' => $page,
                'server_id' => $serverInfo['real_server_id'],
                'server_list' => $server_list,
                'empty' => '<td class="empty" colspan="7">暂无数据</td>',
                'meta_title' => '帮派列表'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 创建帮派
     **/
    public function create()
    {
        $server_list = ServerManage::getServerList();
        if (Request::isPost()) 
        {
            $data = $_POST;
            $server_id = $data['server_id'];
            $gangs_count = $data['gangs_count'];
            action_log('gangs_create', 'gangs', '', UID);
            test::webw_packet_create_system_sect($server_id, $gangs_count);
            $this->success('创建帮派信息提交成功,等待服务器处理......');
        } 
        else
        {
            View::assign([
                'server_id' => '',
                'server_list' => $server_list,
                'meta_title' => '创建帮派信息'
            ]);
            return View::fetch();
        }
    }

    /**
     * 修改帮会公告
     */
    public function notice()
    {
        $sect_id = Request::param('id');
        $sect_name = Request::param('name');
        $server_id = Request::param('server_id');
        $server_list = ServerManage::getServerList();
        if (Request::isPost()) 
        {
            $data = $_POST;
            test::webw_packet_change_sect_notice($data['server_id'], $data['sect_id'], $data['notice_desc']);
            action_log('gangs_edit', 'gangs', $sect_id, UID);
            $this->success('帮派公告信息提交成功,等待服务器处理......');
        } 
        else 
        {
            // $info = dbConfigByReadBase($server_id)->table('sect')
            View::assign([
                'server_id' => $server_id,
                'sect_id' => $sect_id,
                'sect_name' => $sect_name,
                'server_list' => $server_list,
                'meta_title' => '修改帮派公告'
            ]);
            return View::fetch();
        }
    }

    /**
     * 移交掌门
     **/
    public function transfer_master()
    {
        $sect_id = Request::param('id');
        $sect_name = Request::param('name');
        $server_id = Request::param('server_id');
        $server_list = ServerManage::getServerList();
        $where[] = [
            ['sect_id', '=', $sect_id],
            ['title', '<>', 100]
        ];
        $member_list = dbConfigByReadBase($server_id)->table('sect_member')->field('actor_id')->where($where)->select();
        if (Request::isPost())
        {
            $data = $_POST;
            if (!isset($data['actor_id']) || $data['actor_id'] == "0") 
            {
                $this->error('请选择成员');
            }
            test::webw_packet_change_sect_master($data['server_id'], $data['sect_id'], $data['actor_id']);
            action_log('gangs_president_transfer', 'gangs', $sect_id, UID);
            $this->success('帮派会长转移信息提交成功,等待服务器处理...', 'index');
        }
        else 
        {
            View::assign([
                'server_id' => $server_id,
                'sect_id' => $sect_id,
                'sect_name' => $sect_name,
                'server_list' => $server_list,
                'member_list' => $member_list,
                'meta_title' => '帮派掌门移交'
            ]);
            return View::fetch();
        }
    }

    /**
     * 帮派成员
     */
    public function member()
    {
        $guild_id = $this->request->param("id");
        if (!$guild_id) exit('请点击帮派名称获取成员！！！');
        $server_id = $this->request->param('server_id');
        $where[] = ['sect_id', '=', $guild_id];
        $lists = dbConfig(Session::get('server_id'))
            ->table('sect_member')
            ->where($where)
            ->order('title dec,sect_id desc,actor_id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);


        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'server_id' => $server_id,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '帮派成员列表'
        ]);
        return $this->fetch();
    }

    /**
     * 获取帮派成员基础信息
     * @param $actor_id
     * @return
     * @throws \think\Exception
     */
    public function get_member_info($actor_id)
    {
        return dbConfig(\think\facade\Session::get('server_id'))->table('player')->where(['actor_id' => $actor_id])->find();
    }
}
