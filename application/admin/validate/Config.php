<?php

namespace app\admin\validate;

use think\Validate;

class Config extends Validate
{
    protected $rule =   [
        'name' => 'require|max:30',
        'title' => 'require|max:50',
        'extra' => 'max:255',
        'remark' => 'max:200',
        'type' => 'require|number',
        'sort' => 'require|number',
    ];

    protected $message  =   [
        'title.require' => '请输入菜单名称',
        'title.max' => '菜单名称最多不能超过50个字符',
        'extra.max' => '配置值最多不能超过255个字符',
        'remark.max' => '配置说明最多不能超过200个字符',
        'pid.require' => '请选择上级菜单',
        'pid.number' => '请选择有效的上级菜单',
        'sort' => '请输入排序序号',
        'sort.number' => '排序序号只能是数字',
    ];

    //更新排序
    protected $scene = [
        'sort'  =>  ['sort']
    ];
}
