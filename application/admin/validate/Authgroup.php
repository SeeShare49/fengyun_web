<?php

namespace app\admin\validate;

use think\Validate;

class Authgroup extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'title' => 'require|max:20',
        'type' => 'number',
        'description' => 'max:80',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'title.require' => '请输入用户组名称!',
        'title.max' => '用户组名称最多输入20个字符',
        'type.number' => '组类型只能为数字',
        'description.max' => '描述信息最多输入80个字符',
    ];
}
