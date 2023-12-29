<?php


namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command
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
        $this->testWork();
        #计划任务列表 end#
    }

    public function testWork()
    {
        echo time() . '定时执行任务testWork';
    }
}