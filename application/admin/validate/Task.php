<?php

namespace app\admin\validate;

use think\Validate;

class Task extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'=>'require',
        'status'=>'require|number'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'=>'请输入活动任务名称',
        'status.require'=>'请选择活动任务是否显示状态',
    ];
}
