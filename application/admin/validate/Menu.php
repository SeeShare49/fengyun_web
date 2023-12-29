<?php

namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule =   [
        'title' => 'require|max:50',
        'url' => 'max:255',
        'pid' => 'require|number',
        'sort' => 'require|number',
        'is_dev' => 'require|number',
    ];

    protected $message  =   [
        'title.require' => '请输入菜单名称',
        'title.max' => '菜单名称最多不能超过50个字符',
        'url.max' => '链接地址最多不能超过50个字符',
        'pid.require' => '请选择上级菜单',
        'pid.number' => '请选择有效的上级菜单',
        'sort' => '请输入排序序号',
        'sort.number' => '排序序号只能是数字',
        'is_dev' => '请选择可见模式',
        'is_dev.number' => '可见模式只能是数字',
    ];

    //更新排序
    protected $scene = [
        'sort'  =>  ['sort']
    ];
}
