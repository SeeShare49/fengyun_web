<?php

namespace app\admin\validate;

use think\Validate;

class TargetType extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '请输入目标分类名称',

    ];

    public $scene = [
        'ajaxSave' => ['quota', 'reward'],
        'ajaxEdit' => ['quota', 'reward']
    ];

    /**
     * 自定义验证规则
     */
    public function sceneAdd()
    {
        return $this->only(['quota', 'reward']);
    }
}
