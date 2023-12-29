<?php

namespace app\admin\controller;


use think\facade\View;

/**
 * 广告投放统计信息
 **/
class Advertise extends Base
{
    /**
     *  广告投放信息列表
     **/
    public function index()
    {
        View::assign([
            'empty' => '<td class="empty" colspan="24">暂无数据</td>',
            'meta_title' => '广告投放统计信息'
        ]);
        return View::fetch();
    }
}
