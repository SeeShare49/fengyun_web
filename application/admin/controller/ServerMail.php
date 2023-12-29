<?php

namespace app\admin\controller;


use app\admin\model\PropCsv;
use app\common\ServerManage;
use app\common\test;
use think\facade\Request;
use think\facade\View;

class ServerMail extends Base
{
    /**
     * 单多服邮件列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $serverlist = ServerManage::getServerList();
        $title = trim(input('title'));
        $where = [];
        if ($title) {
            $where[] = ['title', 'like', "%$title%"];
        }

        $where[] = ['mail_type', '=', 2];

        $server_id = trim(input('server_id'));

        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        $lists = \app\admin\model\Mail::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'server_id' => $server_id,
            'server_list' => $serverlist,
            'title' => $title,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'meta_title' => '单多服邮件列表'
        ]);
        return $this->fetch();
    }

    /**
     * 邮件发送（指定某个服务器）
     */
    public function server()
    {
        if (Request::isPost()) {
            $data = $_POST;
            if (!isset($data['server_id'])) {
                $this->error('请选择要发送的服务器,至少选择一个区服!!!');
            }

            $data['create_time'] = $_SERVER['REQUEST_TIME'];// time();
            $data['mail_type'] = 2;//全服邮件

            $is_attach = true;//是否纯文字邮件，无附件（无道具信息）
            $status = 1;
            $prop_alias_info = '';//道具列表别名
            if (isset($data['prop_info']) && !empty($data['prop_info'])) {
                $is_attach = false;
                $status = 0;//审核状态（未审核）
                $attach_arr = explode(';', $data['prop_info']);
                $attach_arr_count = count($attach_arr);
                for ($i = 0; $i < $attach_arr_count; $i++) {
                    $attach_info = explode('|', $attach_arr[$i]);
                    if (count($attach_info) > 1) {
                        $prop_alias_info .= PropCsv::where('type_id', '=', $attach_info[0])->value('type_name') . '|' . $attach_info[1] . ';';
                    }
                }
            }

            if ($is_attach == true) {
                $data['send_time'] = $_SERVER['REQUEST_TIME'];
            }
            $data['status'] = $status;
            $data['prop_alias_info'] = rtrim($prop_alias_info, ';');
            $ret = \app\admin\model\Mail::insertGetId($data);
            if ($ret) {
                if ($status == 1) {
                    test::mail_plus($data['server_id'], $data['title'], $data['content'], $data['prop_info']);
                }
                action_log('mail_server_add', 'mail', $ret, UID);
                $this->success("单多服邮件发送成功!", 'server_mail/index');
            } else {
                $this->error("单多服邮件发送失败!");
            }
        } else {
            $server_list = ServerManage::getServerList();
            $server_id = trim(input('server_id'));
            View::assign([
                'server_list' => $server_list,
                'server_id' => $server_id,
                'meta_title' => '单多服邮件发送'
            ]);
            return View::fetch();
        }
    }
}
