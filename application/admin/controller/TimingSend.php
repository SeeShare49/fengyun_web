<?php

namespace app\admin\controller;

use app\common\test;
use think\facade\Log;

class TimingSend
{
    public function index()
    {
        $notice = \app\admin\model\Notice::where('send_type', '=', 1)
            ->whereBetweenTimeField('send_start_time', 'send_end_time')
            ->limit(1)
            ->order('send_start_time asc')
            ->find();
        if ($notice) {
            $start_time = strtotime(date('H:i:s', $notice['send_start_time']));
            $end_time = strtotime(date('H:i:s', $notice['send_end_time']));
            $cur_time = strtotime(date('H:i:s', time()));

            if (intval($cur_time - $start_time) > 0 && intval($end_time - $cur_time) >= 0) {
                Log::write('coming.....');
                test::webw_packet_notice($notice['server_id'], $notice['notice_content']);
//                $sleep_sec = isset($notice['play_interval']) ? $notice['play_interval'] * 60 : 600000;//数据表中配置的间隔时间单位（分钟）
//                sleep($sleep_sec);
                sleep(10);
                $this->result($notice, 1, '公告发送成功,待服务器处理......');
            }
        } else {
            Log::write("当前时间:【" . date('Y-m-d H:i:s', time()) . '】暂无公告');
        }
    }

}
