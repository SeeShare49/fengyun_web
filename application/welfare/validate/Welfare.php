<?php

namespace app\welfare\validate;

use think\Validate;

class Welfare extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'server_id' => ['require', 'number', 'gt:0'],
        'nickname' => 'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'require.server_id'=>'请选择服务器',
        'require.nickname'=>'请输入用户角色名称'
    ];
}
