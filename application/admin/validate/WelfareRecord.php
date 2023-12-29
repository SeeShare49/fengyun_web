<?php

namespace app\admin\validate;

use think\Validate;

class WelfareRecord extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'player_name'=>'require',
        'welfare_id'=>'require|number'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'player_name.require'=>'玩家昵称必须填写',
        'welfare_id.require'=>'福利编号必须填写',
        'welfare_id.number'=>'福利编号格式错误'
    ];
}
