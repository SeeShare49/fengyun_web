<?php

namespace app\admin\validate;

use think\Validate;

class ServerConfig extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'hostname'=>'ip',
        'hostport'=>'number',
        'username'=>'require',
        'password'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'hostname.ip'=>'服务器IP地址格式错误',
        'hostport.number'=>'服务器端口格式错误',
        'username.require'=>'服务器登录名不能为空',
        'possword.require'=>'服务器登录密码不能为空'
    ];
}
