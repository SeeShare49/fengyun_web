<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------



Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

\think\facade\Route::get('/wechat_pay','/pay/WxPay');




//\think\facade\Route::get('/gate','/gate.php/GameLogin/index');

//\think\facade\Route::rule('index','admin/index');
\think\facade\Route::domain('bkb.52yiwan.com','admin');
\think\facade\Route::domain('login.52yiwan.com ','gate');

return [

];
