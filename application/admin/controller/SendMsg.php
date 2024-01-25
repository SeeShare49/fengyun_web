<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/7
 * Time: 11:16
 */

namespace app\admin\controller;

use app\admin\model\PropCsv;

use app\admin\model\SysProp;
use app\admin\validate\AddProp as AddPropValidate;

use app\admin\validate\AddCoin as AddCoinValidate;

use app\common;

use app\common\ServerManage;

use think\facade\Request;
use think\facade\Session;

class SendMsg extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('player_name'));
        $where[] = ['1', '=', 1];
        if ($player_name)
        {
            $where[] = ['player_name', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
//        if (empty($server_id) || $server_id == "0") {
//            $resInfo = ServerManage::getServerInfo();
//            if ($resInfo) {
//                $server_id = $resInfo['id'];
//            }
//        }
//        Session::set("server_id", $server_id);
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        $lists = SysProp::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'player_name' => $player_name,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'lists' => $lists,
            'page' => $page,
            'empty'=>'<td class="empty" colspan="9">暂无数据</td>',
            'meta_title' => '系统添加道具记录列表'
        ]);
        return $this->fetch();
    }

    /**
     * 添加道具
     * 发送添加道具数据到服务器
     * Code:800
     */
    public function prop()
    {
        if (Request::isPost()) 
        {
            $data = $_POST;
            if ($data['server_id'] == "0")
            {
                $this->error("请选择服务器");
            }

            $info = dbConfig($data['server_id'])->table('player')->where('nickname', '=', trim($data['player_name']))->find();

            if (!$info)
            {
                $this->error("昵称:【{$data['player_name']}】玩家不存在,请核实用户昵称!");
            }

            $propValidate = new AddPropValidate();
            if (!$propValidate->check($data)) 
            {
                $this->error($propValidate->getError());
            }
            sys_prop_record($data['server_id'], $data['player_name'], $data['item_id'], $data['item_count']);
            common\test::webw_packet_add_item($data['server_id'], $data['player_name'], $data['item_id'], $data['item_count'], $data['item_bind']);
            return json(['code' => 1, 'msg' => '添加道具请求提交成功,待服务器处理......', 'data' => "norefresh"]);
        } 
        else
        {
            $server_list = ServerManage::getServerList();
            // $prop_list = PropCsv::select();
            $server_id = trim(input('server_id'));
            $type_id = trim(input('item_id'));

            $this->assign([
                'server_list' => $server_list,
                //'prop_list' => $prop_list,
                'server_id' => $server_id,
                'item_id' => $type_id,
                'meta_title' => "系统添加道具"
            ]);
            return $this->fetch();
        }
    }

    /**
     * 添加金币/元宝/钻石
     * 发送添加添加金币/元宝/钻石数据到服务器
     * Code:801
     */
    public function coin()
    {
        if (request()->isPost()) {
            $data = $_POST;
            if ($data['server_id'] == "0") {
                $this->error("请选择服务器");
            }

            $info = dbConfig($data['server_id'])
                ->table('player')
                ->where('nickname', '=', trim($data['player_name']))
                ->find();

            if (!$info) {
                $this->error("昵称:【{$data['player_name']}】玩家不存在,请核实用户昵称!");
            }

            $coinValidate = new AddCoinValidate();
            if (!$coinValidate->check($data)) {
                $this->error($coinValidate->getError());
            }
            common\test::webw_packet_add_money($data['server_id'], $data['player_name'], $data['yuan_bao'], $data['gold'], $data['diamonds']);
            $this->success("添加金币/元宝/钻石请求提交成功,待服务器处理......", 'send_msg/coin');
        } else {
            $serverlist = ServerManage::getServerList();
            $this->assign([
                'serverlist' => $serverlist,
                'meta_title' => '添加金币/元宝/钻石'
            ]);
            return $this->fetch();
        }
    }
}