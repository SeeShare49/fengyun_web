<?php


namespace app\command;

use tests\webw_packet_notice;
use app\admin\controller\Notice;
use app\common\test;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class TimingSend extends Command
{
    //配置定时器信息
    public function configure()
    {
        $this->setName('TimingSend')->setDescription('游戏公告定时发送任务!!!');
    }

    public function execute(Input $input, Output $output)
    {
        //输出信息并换行
        $output->writeln('Date Crontab job start...');

        #计划任务列表 start#
        //$this->testWork();
        \app\admin\controller\Notice::timing_send_notice();
//
//        $notice = \app\admin\model\Notice::where('send_type', '=', 1)
//            ->whereBetweenTimeField('send_start_time', 'send_end_time')
//            ->limit(1)
//            ->order('send_start_time asc')
//            ->find();

//        if ($notice) {
//            Log::write('====================================');
//            dump($notice);
//            $sleep_sec =90;// isset($notice['play_interval']) ? $notice['play_interval'] * 60 : 30;//数据表中配置的间隔时间单位（分钟）
//            sleep($sleep_sec);
//        }


        #计划任务列表 end#
    }

    public function testWork()
    {
        Log::write('timing send start....');
        echo 'timing send start....';
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
                test::webw_packet_notice($notice['server_id'], $notice['notice_content']);
                $sleep_sec = isset($notice['play_interval']) ? $notice['play_interval'] * 60 : 30;//数据表中配置的间隔时间单位（分钟）
                sleep($sleep_sec);
                $this->result($notice, 1, '公告发送成功,待服务器处理......');
            }
        } else {
            Log::write("当前时间:【" . date('Y-m-d H:i:s', time()) . '】暂无公告');
        }
    }

}