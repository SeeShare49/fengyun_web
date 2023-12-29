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
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * 跨服服务器管理类
 */
class KuafuServerManage extends Controller
{
    /***
     * 服务器列表(未被合服且启用状态的服务器)
     */
    public static function getServerList()
    {
        return (new KuafuServerList())->table('kuafu_server_list')->where([['use_status', '=', '1'], ['open_time', '<=', time()]])->field('id,area_id,servername')->select();
    }

    /**
     * 根据对应选择的服务器Id查询服务器列表
     * @param $server_id
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getServerListByIds($server_id)
    {
        return (new KuafuServerList())->table('kuafu_server_list')->where([['id', 'in', $server_id], ['use_status', '=', '1'], ['open_time', '<=', time()]])->field('id,area_id,servername')->select();
    }

    /**
     * 获取根据id升序第一条状态为(status=1)的服务器数据
     */
    public static function getServerInfo()
    {
        try {
            return (new KuafuServerList())->table('kuafu_server_list')
                ->field('id,area_id,servername')
                ->where([['use_status', '=', 1], ['open_time', '<=', time()]])
                ->limit(1)
                ->order('id desc')
                ->find();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
    }

    /**
     * 根据管理员角色权限获取服务器信息
     * @param $server_id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getServerInfoByGuild($server_id)
    {
        return (new \app\admin\model\KuafuServerList)->table('server_list')
            ->field('id,area_id,servername')
            ->where([['id', '=', $server_id], ['use_status', '=', 1], ['open_time', '<=', time()]])
            ->limit(1)
            ->order('id desc')
            ->find();
    }
}