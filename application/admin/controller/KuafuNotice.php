<?php

namespace app\admin\controller;

use app\admin\validate\Notice as NoticeValidate;

use app\admin\model\Notice as NoticeModel;
use app\common\KuafuServerManage;
use app\common\ServerManage;
use app\common\test;

use think\Db;
use think\facade\Log;
use think\facade\View;

class KuafuNotice extends Base
{
    /**
     * 创建跨服公告信息
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
            $data['notice_type'] = 2;//跨服公告
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
                $this->success('跨服公告信息添加成功!', 'notice/index');
            } else {
                $this->error("跨服公告信息添加失败!");
            }
        } else {
            //初始化服务器列表
            $server_list = KuafuServerManage::getServerList();
            $this->assign([
                'server_list' => $server_list,
                'meta_title' => '创建跨服公告信息'
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
            $server_list = KuafuServerManage::getServerList();
            $this->assign([
                'server_list' => $server_list,
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑跨服公告信息'
            ]);
            return $this->fetch();
        }
    }
}
