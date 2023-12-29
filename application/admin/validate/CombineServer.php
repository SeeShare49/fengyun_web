<?php

namespace app\admin\validate;

use think\Validate;

class CombineServer extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'server_id' => 'require',
        'server_id_c' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'server_id.require' => '请选择合服服务器',
        'server_id_c.require' => '请选择被合服服务器',
    ];
}
