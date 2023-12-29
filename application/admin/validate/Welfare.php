<?php

namespace app\admin\validate;

use think\Validate;

class Welfare extends Validate
{
    /**
     * 福利中心
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require',
//        'content' => 'require',
        // 'cycle' => ['number', 'gt:0'],
//        'welfare_type' => ['require' ]
    ];

    /**
     * 福利中心
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'title.require' => '请输入礼包标题',
//        'content.require' => '请输入礼包内容',
        'cycle.number' => '福利领取周期格式错误',
        // 'cycle.gt' => '福利领取周期必须大于0',
//        'welfare_type.require' => '请选择福利类型',
    ];
}
