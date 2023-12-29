<?php

namespace app\admin\validate;

use think\Validate;

class RechargeConfig extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'money'=>'require|number',
        'amount'=>'require|number',
        'recharge_type'=>'require|number',
        'attach_amount'=>'number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'money.require'=>'请输入充值金额',
        'money.number'=>'充值金额必须是正整数',
        'amount.require'=>'请输入充值元宝数量',
        'amount.number'=>'充值元宝数量必须大于1的有效数字',
        'recharge_type.require'=>'请选择充值类型',
        'recharge_type.number'=>'充值类型错误',
        'attach_amount.number'=>'附加道具数量必须为有效数字',
    ];
}
