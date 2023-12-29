<?php

namespace app\admin\validate;

use think\Validate;

class ChannelShare extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'channel_id' => ['require', 'number', 'gt:0']
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'channel_id.require' => '请选择渠道',
        'channel_id.number' => '渠道ID必须是数字',
        'channel_id.gt' => '渠道ID必须大于0'
    ];
}
