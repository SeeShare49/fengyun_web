<?php

namespace app\admin\validate;

use think\Validate;

class KuafuServer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id' => 'require|number',
        'servername' => 'require|max:50',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'id.require' => '请输入区服ID',
        'id.number' => '区服ID只能输入数字',
        'servername.require' => '请输入服务器名称',
        'servername.max' => '服务器名称最多不能超过50个字符',
    ];
}
