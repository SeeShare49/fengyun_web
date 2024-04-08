<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/7
 * Time: 15:27
 */

namespace think;

//define('__ROOT__', './');

//define('BIND_MODULE','admin');.

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象


// 执行应用并响应
Container::get('app')->run()->send();
