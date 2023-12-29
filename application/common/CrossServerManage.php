<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/10
 * Time: 11:49
 */

namespace app\common;

use app\admin\model\KuafuServerList;
use think\Controller;

class CrossServerManage extends Controller
{
    /***
     * 服务器列表(未被合服且启用状态的服务器)
     */
    public static function getCrossServerList()
    {
        return (new KuafuServerList())->table('kuafu_server_list')->order('id asc')->field('id,servername')->select();
    }
}