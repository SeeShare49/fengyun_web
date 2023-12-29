<?php

namespace app\admin\validate;

use think\Validate;

class UserInfo extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'ip_limit'=>'require|ip'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'ip_limit'=>'IP地址格式错误'
    ];

    //IP限制
    protected $scene = [
        'ip_limit' => ['ip_limit']
    ];

    public function sceneIpLimit()
    {
        return $this->only(['ip_limit']);
    }
}
