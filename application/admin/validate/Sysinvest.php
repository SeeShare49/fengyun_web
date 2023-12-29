<?php

namespace app\admin\validate;

use think\Validate;

class Sysinvest extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'server_id'=>'require|number',
	    'player_name'=>'require',
        'gold'=>'require|number|between:0,1000000000',
        'yuan_bao'=>'require|number|between:0,1000000000',
        'diamonds'=>'require|number|between:0,1000000000',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'server_id.require'=>'请选择游戏服务器',
        'player_name.require'=>'请输入玩家昵称',
        'gold.require'=>'请输入金币数量',
        'gold.number'=>'金币数量只能是数值',
        'gold.between'=>'金币数量限定范围0-1000000000',
        'yuan_bao.require'=>'请输入元宝数量',
        'yuan_bao.number'=>'元宝数量只能是数值',
        'yuan_bao.between'=>'元宝数量限定范围0-1000000000',
        'diamonds.require'=>'请输入钻石数量',
        'diamonds.number'=>'银票数量只能是数值',
        'diamonds.between'=>'银票数量限定范围0-1000000000',

    ];
}
