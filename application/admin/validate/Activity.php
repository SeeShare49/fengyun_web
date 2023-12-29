<?php

namespace app\admin\validate;

use think\Validate;

class Activity extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require|max:100',
        'activity_type' => ['require', 'number', 'gt:0'],
        'task_id' => ['require', 'number', 'gt:0'],
        'target_id' => ['require', 'number', 'gt:0'],
        'server_id' => ['require', 'number', 'gt:0'],
        'active_cycle' => ['require', 'number', 'gt:0'],
        'start_time' => 'require',
        'end_time' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '请输入活动名称',
        'name.max' => '活动名称最多输入100字符',
        'activity_type.require' => '请选择活动类型',
        'activity_type.number' => '活动类型必须是数字',
        'activity_type.gt' => '活动类型数值必须大于0',
        'task_id.require' => '请选择任务类型',
        'task_id.number' => '任务类型必须是数字',
        'task_id.gt' => '任务类型数值必须大于0',
        'server_id.require' => '请选择服务器',
        'server_id.number' => '服务器ID必须是数字',
        'server_id.gt' => '服务器ID数值必须大于0',
        'active_cycle.require' => '请输入活动周期',
        'active_cycle.number' => '活动周期必须是数字',
        'active_cycle.gt' => '活动周期数值必须大于0',
        'start_time.require' => '请输入活动起始日期',
        'end_time.require' => '请输入活动截止日期',
    ];
}
