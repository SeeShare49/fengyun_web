<?php

namespace app\admin\validate;

use think\Validate;

class Target extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];

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
