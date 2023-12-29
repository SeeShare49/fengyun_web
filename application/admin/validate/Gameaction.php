<?php

namespace app\admin\validate;

use think\Validate;

class GameAction extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name'=>'require',
        'title'=>'require',
        'value'=>'require|number'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'=>'请输入行为标识',
        'title.require'=>'请输入行为名称',
        'value.require'=>'请输入行为值',
        'value.number'=>'行为值只能输入数字'
    ];
}
