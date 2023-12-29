<?php

namespace app\admin\controller;

use app\admin\validate\Notice as NoticeValidate;
use app\admin\model\Notice as NoticeModel;
use app\common\ServerManage;
use app\common\notice_test;
use app\common\test;

use think\Db;
use think\facade\Log;
use think\facade\View;

class Notice extends Base
{
    /**
     * 公告列表
     * @return \think\Response
     */
    public function index()
    {
        $notice_content = trim(input('notice_content'));
        $where[] = ['1', '=', 1];
        /** 发送方式（1定时发送，2即时发送） **/
        $send_type = trim(input("send_type"));
        if ($send_type && $send_type != -1) {
            $where[] = ['send_type', '=', $send_type];
        }

        /** 公告类型 1、系统公告；2跨服公告 **/
        $notice_type = trim(input("notice_type"));
        if ($notice_type && $notice_type != -1) {
            $where[] = ['notice_type', '=', $notice_type];
        }

        if ($notice_content) {
            $where[] = ['notice_content', 'like', "%$notice_content%"];
        }

        $lists = NoticeModel::where($where)
            ->order('send_type asc,id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'notice_content' => $notice_content,
            'lists' => $lists,
            'page' => $page,
            'send_type' => $send_type,
            'notice_type' => $notice_type,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '公告信息'
        ]);
        return $this->fetch();
    }

    /**
     * 创建公告信息
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $noticeValidate = new NoticeValidate();
            if (!$noticeValidate->check($data)) {
                $this->error($noticeValidate->getError());
            }
            $data['send_start_time'] = strtotime($data['send_start_time']);
            $data['send_end_time'] = strtotime($data['send_end_time']);

            $re = NoticeModel::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("notice_add", "notice", $re, UID);
                //发送方式为即时发送（send_type=2）发送命令到服务器
                if (isset($data['send_type']) && $data['send_type'] == 2) {
                    //即时公告发送服务器
                    test::webw_packet_notice($data['server_id'], $data['notice_content']);
                } else {
                    $this->auto_send_notice($re);
                }
                $this->success('公告信息添加成功!', 'notice/index');
            } else {
                $this->error("公告信息添加失败!");
            }
        } else {
            //初始化服务器列表
            $server_list = ServerManage::getServerList();
            $this->assign([
                'server_list' => $server_list,
                'meta_title' => '创建公告信息'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 定时公告发送服务器
     * @param $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function auto_send_notice($id)
    {
        Log::write("auto send notice operate time:" . time());
        Log::write("auto send notice coming...");
        Log::write("auto send notice id:" . $id);
        $where[] = [
            ['id', '=', $id],
            ['send_type', '=', 1],
            ['status', '=', 1]
        ];
        $info = \app\admin\model\Notice::where($where)->find();

        $send_data = array();
        if ($info) {
            $server_id = $info['server_id'];
            Log::write("info is not null....");
            Log::write("info server_id:" . $server_id);
            $server_ids = explode(',', rtrim($server_id, ","));
            for ($j = 0; $j < count($server_ids); $j++) {
                $data['server_id'] = $server_ids[$j];
                $data['notice_id'] = $info['id'];
                $data['notice'] = $info['notice_content'];
                $data['start_time'] = $info['send_start_time'];
                $data['end_time'] = $info['send_end_time'];
                $data['interval_time'] = $info['play_interval'] * 60;
                array_push($send_data, $data);
            }

            Db::connect('db_config_main')->table('auto_notice')->where('notice_id', '=', $id)->delete();
            $result = Db::connect('db_config_main')->table('auto_notice')->insertAll($send_data);
            if ($result) {
                $ids = str_replace(',', '|', $server_id);
                Log::write("定时公告发送至服务器成功！！！,ids:" . $ids);
                test::webw_packet_game_notice($ids);
                Log::write("!!!!定时公告发送至服务器成功！！！");
            } else {
                Log::write("定时公告发送至服务器失败！！！");
            }
        } else {
            Log::write("notice info is null!!!!!!!!!");
        }
    }


    /**
     * 定时执行公告发送
     * 用户windows下的游戏公告定时发送
     */
    public function timing_send_notice()
    {
        ignore_user_abort(true);    //设定关闭浏览器也执行程序
        set_time_limit(0);        // 设定响应时间不限制，默认为30秒
        ini_set('memory_limit', '512M');
        while (true) {
            if (connection_status() != CONNECTION_NORMAL) {
                break;
            }

            $where[] = [
                ['send_type', '=', 1],
                ['status', '=', 1]
            ];
            $notice_list = \app\admin\model\Notice::where($where)
                ->whereBetweenTimeField('send_start_time', 'send_end_time')
                ->order('send_start_time asc')
                ->select();

            $notice_count = count($notice_list);
            $sleep_sec = 300;
            if ($notice_count > 0) {
                for ($i = 0; $i < $notice_count; $i++) {
                    Log::write('notice 定时公告存在,待发送......');
                    $start_time = $notice_list[$i]['send_start_time'];
                    $end_time = $notice_list[$i]['send_end_time'];
                    $cur_time = time();

                    if (intval($cur_time - $start_time) > 0 && intval($end_time - $cur_time) >= 0) {
                        Log::write('定时公告满足当前时间条件,待发送......,定时公告发送时间:' . date('H:i:s', time()));
                        test::webw_packet_notice($notice_list[$i]['server_id'], $notice_list[$i]['notice_content']);
                        Log::write('定时公告发送成功!!!定时公告发送成功时间:' . date('H:i:s', time()));
//                     $this->result($notice, 1, '公告发送成功,待服务器处理......');
                        $sleep_sec = isset($notice['play_interval']) ? $notice['play_interval'] * 60 : 300;//数据表中配置的间隔时间单位（分钟）
                    }
                }
                sleep($sleep_sec);
            } else {
                Log::write("当前时间:【" . date('Y-m-d H:i:s', time()) . '】暂无公告');
            }
        }
    }


    /**
     * 用于linux系统下的crontab 定时发送游戏公告
     */
    public function linux_timing_send_notice()
    {
        $where[] = [
            ['send_type', '=', 1],
            ['status', '=', 1]
        ];
        $notice = \app\admin\model\Notice::where($where)
            ->whereBetweenTimeField('send_start_time', 'send_end_time')
            ->limit(1)
            ->order('send_start_time asc')
            ->find();
        if ($notice) {
            Log::write('linux crontab notice 定时公告存在,待发送......');
            $start_time = $notice['send_start_time'];
            $end_time = $notice['send_end_time'];
            $cur_time = time();

            if (intval($cur_time - $start_time) > 0 && intval($end_time - $cur_time) >= 0) {
                Log::write('linux crontab 定时公告满足当前时间条件,待发送......,定时公告发送时间:' . date('H:i:s', time()));
                test::webw_packet_notice($notice['server_id'], $notice['notice_content']);
                Log::write('linux crontab 定时公告发送成功!!!定时公告发送成功时间:' . date('H:i:s', time()));
            }
        } else {
            Log::write("linux crontab 当前时间:【" . date('Y-m-d H:i:s', time()) . '】暂无公告');
        }
    }

    /**
     * 编辑公告信息
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $info = NoticeModel::find($id);
        if (!$info) {
            $this->error("公告信息不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $noticeValidate = new NoticeValidate();
            if (!$noticeValidate->check($data)) {
                $this->error($noticeValidate->getError());
            }

            $data['send_start_time'] = strtotime($data['send_start_time']);
            $data['send_end_time'] = strtotime($data['send_end_time']);

            $re = NoticeModel::update($data);
            if ($re) {
                action_log("notice_edit", "notice", $re, UID);
                if (isset($info['send_type']) && $info['send_type'] == 2) {
                    //即时公告发送服务器
                    test::webw_packet_notice($info['server_id'], $info['notice_content']);
                } else {
                    $this->auto_send_notice($id);
                }
                $this->success('公告信息编辑成功!', 'notice/index');
            } else {
                $this->error("公告信息编辑失败!");
            }
        } else {
            $server_list = ServerManage::getServerList();
            $this->assign([
                'server_list' => $server_list,
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑公告信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定资源
     * @return \think\Response
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $res = NoticeModel::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("notice_del", "notice", $ids, UID);
            Db::connect('db_config_main')->table('auto_notice')->where('notice_id', 'in', $ids)->delete();
            test::webw_packet_game_notice(1);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 启用禁用公告信息
     */
    public function set_notice_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $notice_status = "notice_status_show";
            if ($data['status'] == 0) $notice_status = "notice_status_hide";

            $res = NoticeModel::update($data);
            if ($res) {
                //添加行为记录
                action_log($notice_status, "notice", $data['id'], UID);
                $this->success('公告状态修改成功！');
            } else {
                $this->error('公告状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    public function refresh()
    {
        return View::fetch();
    }
}
