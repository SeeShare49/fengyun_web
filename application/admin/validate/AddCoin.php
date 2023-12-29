<?php

namespace app\admin\validate;

use think\Validate;

class AddCoin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'server_id' => 'require|number',
        'player_name' => 'require',
        'yuan_bao' => 'require|number',
        'gold' => 'require|number',
        'diamonds' => 'require|number',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'server_id.require' => '请选择服务器ID',
        'server_id.number' => '服务器ID只能为数字',
        'player_name.require' => '请输入玩家昵称',
        'yuan_bao.require' => '请输入元宝数量',
        'yuan_bao.number' => '元宝数量只能为数字',
        'gold.require' => '请输入金币数量',
        'gold.number' => '金币数量只能为数字',
        'diamonds.require' => '请输入钻石数量',
        'diamonds.number' => '钻石数量只能为数字',
    ];
}
