<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/11
 * Time: 18:28
 */


namespace app\behavior;

use think\Facade;
use think\Loader;
use Config;


class LoaderFacadeBehavior
{
    public function run()
    {
        // 注册自定义工具facade类
        Facade::bind(Config::get('facade.facade'));
        // 注册自定义别名
        Loader::addClassAlias(Config::get('facade.alias'));
    }
}