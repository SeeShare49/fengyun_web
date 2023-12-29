<?php
require_once __DIR__."/Timer.php";
require_once __DIR__."/DoJob.php";



\app\common\Timer::dellAll();

\app\common\Timer::add( 1, array('DoJob','job'), array(),true);

\app\common\Timer::add( 3, array('DoJob','job'),array('a'=>1), false);

echo "Time start: ".time()."\n";
\app\common\Timer::run();

while(1)
{
    sleep(1);
    pcntl_signal_dispatch();
}