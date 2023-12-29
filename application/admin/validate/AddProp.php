<?php

namespace app\admin\validate;

use think\Validate;

class AddProp extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'server_id' => 'require|number',
        'player_name' => 'require',
        'item_id' => ['require', 'number', 'gt:0'],
        'item_count' => ['require', 'number', 'gt:0'],
        'item_bind' => 'require|number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'server_id.require' => '请选择服务器ID',
        'server_id.number' => '服务器ID只能为数字',
        'player_name.require' => '请输入玩家昵称',
        'item_id.require' => '请输入道具ID',
        'item_id.number' => '道具ID只能为数字',
        'item_id.gt' => '道具ID只能为大于0的数字',
        'item_count.require' => '请输入道具数量',
        'item_count.number' => '道具数量只能为数字',
        'item_count.gt' => '道具ID只能为大于0的数字',
        'item_bind.require' => '请输入是否绑定',
        'item_bind.require' => '是否绑定只能输入数字',
    ];
}
