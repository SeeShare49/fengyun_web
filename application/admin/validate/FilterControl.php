<?php

namespace app\admin\validate;

use think\Validate;

class FilterControl extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'filter_str' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'filter_str.require' => '请输入过滤关键字|关键词'
    ];
}
