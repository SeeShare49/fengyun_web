<?php

namespace app\admin\validate;

use think\Validate;


class Notice extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'notice_content' => 'require',
        //'play_position'=>'require|number',
        'server_id' => 'require',
        'play_interval' => 'number',
        'status' => 'number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'notice_content.require' => '请输入公告内容',
        //'play_position.number'=>'请选择公告播放位置且只能为数字',
        'play_interval.number' => '请选择公告播放间隔且只能为数字',
        'status.number' => '行为状态只能为数字',
        'server_id' => '请选择服务器'
    ];
}
