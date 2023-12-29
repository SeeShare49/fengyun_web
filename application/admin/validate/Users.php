<?php

namespace app\admin\validate;

use think\Validate;

class Users extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'username' => 'require|max:16',
        'nickname' => 'max:10',
        'password' => 'require|min:4|max:30',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'username.require' => '请输入用户名',
        'username.max' => '用户名最多不能超过16个字符',
        'nickname.max' => '用户名最多不能超过10个字符',
        'password.require' => '请填写密码',
        'password.min' => '密码最少不能低于4个字符',
        'password.max' => '密码最多不能超过30个字符',
    ];

    //验证场景
    protected $scene = [
        'editpwd'  =>  ['password'],//修改密码
        'resetpwd'  =>  ['password'],//修改密码
        'edit'     =>  ['username','nickname'],
    ];
}
