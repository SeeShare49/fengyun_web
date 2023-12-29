<?php

namespace app\admin\controller;

use app\common\ChannelManage;
use app\common\ServerManage;
use app\common\test;
use Config;
use Session;
use think\Db;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;


define('GROUP_ID', config('admin.GROUP_ID'));

class Player extends Base
{
    /**
     * 玩家列表
     */
    public function index()
    {
//        if (GROUPID == GROUP_ID) {
//            $s_ids = get_user_server_list(UID);
//            $temp_server_ids = '';
//            foreach ($s_ids as $key => $value) {
//                $temp_server_ids .= $value['server_id'] . ',';
//            }
//            $ids = explode(',', rtrim($temp_server_ids, ","));
//            $server_list = ServerManage::getServerListByIds($ids);
//        } else {
//            $server_list = ServerManage::getServerList();
//        }
        $server_list = ServerManage::getServerList();
        $channel_list = ChannelManage::getChannelList();

        $nickname = trim(input('nickname'));
        $server_id = trim(input('server_id'));
        $channel_id = trim(input('channel_id'));

//        if (empty($server_id) || $server_id == "0") {
//            if (GROUPID == GROUP_ID) {
//                $s_id = get_user_server_by_id(UID);
//                $resInfo = ServerManage::getServerInfoByGuild($s_id);
//                if ($resInfo) {
//                    $server_id = $resInfo['id'];
//                }
//            } else {
//                $resInfo = ServerManage::getServerInfo();
//                if ($resInfo) {
//                    $server_id = $resInfo['id'];
//                }
//            }
//        }

        if (empty($server_id)) {
            $resInfo = ServerManage::getServerInfo();
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
        }


        if ($channel_id) {
            $where[] = ['u.ChannelID', '=', $channel_id];
        }
        Session::set("server_id", $server_id);
        $where[] = ['u.ChannelID', '<>', 1];
        if ($nickname) {
            $where[] = ['nickname', 'like', "%$nickname%"];
        }

        $actor_id = trim(input('actor_id'));
        if (isset($actor_id) && !empty($actor_id)) {
            $where[] = ['actor_id', '=', $actor_id];
        }

        $account_id = trim(input('account_id'));
        if (isset($account_id) && !empty($account_id)) {
            $where[] = ['account_id', '=', $account_id];
        }

        $start_level = trim(input('start_level'));
        $end_level = trim(input('end_level'));


        if ((!empty($start_level) && $start_level > 0)
            && (!empty($end_level) && $end_level > 0)
            && $end_level > $start_level) {
            $where[] = ['level', 'between', [$start_level, $end_level]];
        } elseif ((!empty($start_level) && $start_level > 0) && (empty($end_level))) {
            $where[] = ['level', '>=', $start_level];
        } elseif ((empty($start_level)) && (!empty($end_level) && $end_level > 0)) {
            $where[] = ['level', '<=', $end_level];
        }

        $is_online = trim(input('online'));
        if ($is_online && $is_online != -1) {
            if ($is_online == 100) {
                $where[] = ['online', '=', 0];
            } else {
                $where[] = ['online', '=', $is_online];
            }
        }

        $off_line = trim(input('off_line'));
        if ($off_line && $off_line > 0) {
            $diff_stamp = time() - $off_line * 3600;
            $where[] = ['last_logout_time', '<=', $diff_stamp];
        }

        $field = 'actor_id,account_id,nickname,level,job,gender,last_login_ip,last_logout_time,online,u.ChannelID,u.register_ip,forbid_chat,deleted,create_time';
        $lists = dbConfig($server_id)
            ->table('player')
            ->field($field)
            ->join('cq_main.user_info u', 'player.account_id = u.UserID')
            ->where($where)
            ->order('actor_id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'server_list' => $server_list,
            'channel_list' => $channel_list,
            'is_online' => $is_online,
            'server_id' => $server_id,
            'channel_id' => $channel_id,
            'nickname' => $nickname,
            'account_id' => $account_id,
            'actor_id' => $actor_id,
            'start_level' => $start_level,
            'end_level' => $end_level,
            'off_line' => $off_line,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="17">暂无数据</td>',
            'meta_title' => '玩家列表'
        ]);
        return $this->fetch();
    }

    /**
     * 通过id获取玩家基本信息
     */
    public function show()
    {
        $actor_id = getParam('id');
        if (!$actor_id) $this->error('请点击用户名选定玩家！！！');

        $where[] = ['actor_id', '=', $actor_id];
        $type = getParam('type');// $this->request->param("type");
        $info = null;
        $server_id = \think\facade\Session::get('server_id');
        switch ($type) {
            case 'baseinfo':
                $info = dbConfigByReadBase($server_id)
                    ->table('player')
                    ->find($actor_id);
                break;
            case 'package'://背包
                $info = dbConfigByReadBase($server_id)
                    ->table('player_item')
                    ->field('ident_id,actor_id,type_id,bag_index,sum(number) as number')
                    ->where(['actor_id' => $actor_id, 'bag_index' => 0])
                    ->group('type_id')
                    ->order('position desc')
                    ->select();
                break;
            case 'skill'://技能
                $info = dbConfigByReadBase($server_id)
                    ->field('actor_id,skill_id,idx,level,exp')
                    ->table('player_skill')
                    ->where(['actor_id' => $actor_id])
                    ->order('skill_id desc')
                    ->select();
                break;
            case 'equip'://装备
                $info = dbConfigByReadBase($server_id)
                    ->table('player_item')
                    ->field('ident_id,actor_id,type_id,bag_index,sum(number) as number')
                    ->where([['actor_id', '=', $actor_id], ['bag_index', 'in', [1, 2]]])
                    ->group('type_id')
                    ->order('position desc')
                    ->select();
                break;
            case 'store'://仓库
                $info = dbConfigByReadBase($server_id)
                    ->table('player_item')
                    ->field('ident_id,actor_id,type_id,bag_index,sum(number) as number')
                    ->where(['actor_id' => $actor_id, 'bag_index' => 3])
                    ->group('type_id')
                    ->order('position desc')
                    ->select();
                break;
            case 'clothes'://时装
                $info = dbConfigByReadBase($server_id)
                    ->table('appearance')
                    ->field('actor_id,appearance_id,limit_time')
                    ->where([['actor_id', '=', $actor_id], ['appearance_id', 'in', [52, 53, 54, 56, 413, 1001, 1002, 1003, 1004]]])
                    ->order('appearance_id asc')
                    ->select();
                break;
            case 'gem'://宝石
                $info = dbConfigByReadBase($server_id)
                    ->table('player_item')
                    ->field('ident_id,actor_id,type_id,bag_index,sum(number) as number')
                    ->where([['actor_id', '=', $actor_id], ['bag_index', 'in', [7, 8]]])
                    ->group('type_id')
                    ->order('position desc')
                    ->select();
                break;
            case 'economy'://经济
                $info = dbConfigByReadBase($server_id)
                    ->field('actor_id,nickname,yuanbao,gold,diamonds')
                    ->table('player')
                    ->where(['actor_id' => $actor_id])
                    ->select();
                break;
        }
        $this->assign([
            'server_id' => $server_id,
            'actor_id' => $actor_id,
            'info' => $info,
            'meta_title' => '角色基本信息'
        ]);
        return $this->fetch($type);
    }

    /**
     * 封号操作
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['actor_id', 'in', $ids];
        $data['deleted'] = 1;
        $res = dbConfig(Session::get('server_id'))
            ->table('player')
            ->where($where)
            ->update($data);
        if ($res) {
            //添加行为记录
            action_log("player_del", "player", $ids, UID);
            $this->success('用户角色信息删除成功');
        } else {
            $this->error('用户角色信息删除失败！');
        }
    }

    /**
     *批量解封
     */
    public function revoke()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['actor_id', 'in', $ids];
        $data['deleted'] = 0;
        $res = dbConfig(Session::get('server_id'))
            ->table('player')
            ->where($where)
            ->update($data);
        if ($res) {
            //添加行为记录
            action_log("player_revoke", "player", $ids, UID);
            $this->success('解封成功');
        } else {
            $this->error('解封失败！');
        }
    }

    /**
     * 管理员手动禁言
     * @param $nickname
     * @throws \think\Exception
     */
    public function set_forbidden_chat()
    {
        if (request()->isPost()) {
            $data['nickname'] = input('nickname');
            $data['forbid_chat'] = input('val');

            $info = dbConfig(Session::get('server_id'))->table('player')->where('nickname', '=', $data['nickname'])->find();
            if (!$info) {
                $this->error("角色名称:【" . $data['nickname'] . "】不存在!!!");
            } else {
                test::webw_packet_forbidden_chat(Session::get('server_id'), $data['nickname'], $data['forbid_chat']);
                action_log('gm_set', "userinfo", $data['nickname'], UID);
                $this->success("角色名称:【" . $data['nickname'] . "】是否禁言设置成功!!!");
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 删除角色
     * 修改角色表deleted字段置为 1
     * @param $id 角色ID
     * @throws \think\Exception
     */
    public function del_role($id)
    {
        if (isset($id)) {
            $server_id = Session::get('server_id');
            $info = dbConfig($server_id)->table('player')->where('actor_id', '=', $id)->find();
            if ($info) {
                $data['deleted'] = 1;
                if (dbConfig($server_id)->table('player')->where('actor_id', '=', $id)->update($data)) {
                    $this->success("角色ID【" . $id . "】删除成功!");
                } else {
                    $this->error("角色ID【" . $id . "】删除失败!");
                }

            } else {
                $this->error("不存在角色ID【" . $id . "】的玩家信息!");
            }
        } else {
            $this->error("角色ID参数错误!");
        }
    }

    /**
     * 恢复角色
     * 修改角色表deleted字段置为 0
     * @param $id 角色ID
     * @throws \think\Exception
     */
    public function recover_role($id)
    {
        if (isset($id)) {
            $server_id = Session::get('server_id');
            $info = dbConfig($server_id)->table('player')->where('actor_id', '=', $id)->find();
            if ($info) {
                $data['deleted'] = 0;
                if (dbConfig($server_id)->table('player')->where('actor_id', '=', $id)->update($data)) {
                    $this->success("角色ID【" . $id . "】恢复成功!");
                } else {
                    $this->error("角色ID【" . $id . "】恢复失败!");
                }

            } else {
                $this->error("不存在角色ID【" . $id . "】的玩家信息!");
            }
        } else {
            $this->error("角色ID参数错误!");
        }
    }


    /**
     * 删除玩家身上道具
     * @param $server_id
     * @param $actor_id
     * @param $guid
     * @throws \think\Exception
     */
    public function del($server_id, $actor_id, $guid)
    {
        if ($server_id && $actor_id && $guid) {
            $info = dbConfigByReadBase($server_id)->table('player')->where('actor_id', '=', $actor_id)->find();
            if ($info) {
                $checkInfo = dbConfigByReadBase($server_id)->table('player_item')->where([['ident_id', '=', $guid], ['actor_id', '=', $actor_id]])->find();
                $itemInfo = dbConfig($server_id)->table('player_item')->where('ident_id', '=', $guid)->delete();
                if ($itemInfo) {

                    $deduct['server_id'] = $server_id;
                    $deduct['user_id'] = $info['account_id'];
                    $deduct['actor_id'] = $actor_id;
                    $deduct['nick_name'] = $info['nickname'];
                    $deduct['prop_type'] = $checkInfo['type_id'];
                    $deduct['ingot'] = $checkInfo['number'];
                    $deduct['operator'] = USERNAME;
                    $deduct['create_time'] = time();
                    \app\admin\model\DeductIngot::insert($deduct);

                    $data['BanReason'] = "";
                    test::webw_packet_ban_user($info['account_id'], 0, "");
                    $this->success("跟踪ID:【" . $guid . "】道具数据信息删除成功,待服务器处理......");
                } else {
                    $this->error("跟踪ID:【" . $guid . "】道具数据信息删除失败!!!");
                }
            } else {
                $this->error("服务器ID:【" . $server_id . "】不存在角色ID:【" . $actor_id . "】玩家信息!!!");
            }
        }
    }

    /**
     * 扣除元宝
     */
    public function deduct_ingot()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $ret = dbConfig($data['server_id'])->table('player')->where('actor_id', '=', $data['actor_id'])->dec('yuanbao', intval($data['ingot']))->update();
            if ($ret) {
                $deduct['server_id'] = $data['server_id'];
                $deduct['user_id'] = $data['user_id'];
                $deduct['actor_id'] = $data['actor_id'];
                $deduct['nick_name'] = $data['nickname'];
                $deduct['prop_type'] = 103;
                $deduct['ingot'] = $data['ingot'];
                $deduct['operator'] = USERNAME;
                $deduct['create_time'] = time();
                \app\admin\model\DeductIngot::insert($deduct);
                test::webw_packet_ban_user($data['actor_id'], 0, "");
                $this->success("玩家【" . $data['nickname'] . "】扣除【" . $data['ingot'] . "】元宝数据提交成功,待服务器处理......");
            } else {
                $this->success("玩家【" . $data['nickname'] . "】扣除【" . $data['ingot'] . "】元宝数据失败......");
            }
        } else {
            $server_id = Request::param('server_id');
            $user_id = Request::param('user_id');
            $actor_id = Request::param('actor_id');
            $nickname = Request::param('nickname');

            //身上最大元宝数量
            $max_ingot = dbConfigByReadBase($server_id)->table('player')->where('actor_id', '=', $actor_id)->value('yuanbao');
            if ($max_ingot <= 1000) {
                $this->error("玩家【" . $nickname . "】仅有【" . $max_ingot . "】元宝,高抬贵手就别难为人家了...");
            }
            View::assign([
                'server_id' => $server_id,
                'user_id' => $user_id,
                'actor_id' => $actor_id,
                'nickname' => $nickname,
                'max_ingot' => $max_ingot,
                'meta_title' => '扣除玩家身上元宝'
            ]);
            return View::fetch();
        }
    }
}
