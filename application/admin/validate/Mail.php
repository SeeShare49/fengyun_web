<?php

namespace app\admin\validate;

use think\Validate;

class Mail extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        //'actor_id'=>'require|number',
        'title' => 'require|max:100',
        'content' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'title.require' => '请输入邮件标题',
        'title.max' => '邮件标题最大长度100字符',
        'content.require' => '请输入邮件内容',
    ];
}
