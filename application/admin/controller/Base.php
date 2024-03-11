<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/7
 * Time: 15:00
 */

namespace app\admin\controller;

use app\admin\model\PropCsv;
use think\Controller;
use think\facade\Log;

class Base extends Controller
{
    protected function initialize()
    {
        //验证用户凭证
        $uid = session('uid');
        if (!$uid)
        {
            $this->error('请登录!', '/admin.php/login','',1);
        }

        $admin = db('users')->field('id,username,status,group_id')->find($uid);
        if (!$admin) 
        {
            $this->logout();
            //$this->error('错误:当前用户不存在或已删除!', 'login/index');
        }

        //判断权限，加载菜单
        //判断超级管理员
        if ($admin['id'] != 1) {
            if ($admin['status'] == -1) {
                $this->logout();
                $this->error('错误:当前用户不存在或已删除!', '/login/index');
            }

            if ($admin['status'] == 0) {
                $this->logout();
                $this->error('错误：当前登录用户已被禁用，请联系系统管理员！', '/login/index');
            }

            if ($admin['group_id'] == 0) {
                $this->logout();
                $this->error('错误：当前登录用户未被分配到任何权限组！', '/login/index');
            }

            //判断权限
            $authGroup = $this->getRules($admin['group_id']);
            if (!$authGroup) {
                $this->logout();
                $this->error('错误：无法获取用户组权限！', '/login/index');
            }
            if ($authGroup['status'] < 1) {
                $this->logout();
                $this->error('错误：用户权限组已被禁用，请联系管理员！', '/login/index');
            }
            if (!$authGroup['rules']) {
                $this->logout();
                $this->error('错误：用户权限组没有分配权限！', null, 'stop');
            }
            if (!$this->checkAuth($authGroup['rules'])) {
                $this->error('错误：没有权限！', null, 'stop');
            }

//            if (!$this->checkAllowIP($authGroup)) {
//                $this->error('错误：您的IP不在访问范围内，请联系管理员！','login/index');
//            }
            //加载菜单
            $adminMenuList = $this->getMenus($authGroup['rules']);
        } else {
            $adminMenuList = $this->getMenus();
        }
        /* 系统菜单配置加载 */
        $this->assign('__MENU__', $adminMenuList);

        /* 读取数据中的道具配置 */
        //$prop_list = PropCsv::select();
        $prop_list = db('','db_table_config')->table('ItemDef')->field('Id AS type_id,Name AS type_name')->select();
        $this->assign('__PROP__', $prop_list);

        /* 读取数据库中的配置 */
        $config = cache('DB_CONFIG_DATA_ADMIN');

        if (!$config) {
            $config = get_db_config(2);
            //读取版本号
            $vinfo = get_hula_version();

            if ($vinfo)
                $config['HULA_VERSION'] = isset($vinfo->version) ? $vinfo->version : '';

            cache('DB_CONFIG_DATA_ADMIN', $config);
        }
        //动态添加配置
        config($config, 'app');

        //定义用户id常量
        define('UID', $uid);
        define('USERNAME', $admin['username']);
        define('GROUPID', $admin['group_id']);
    }


    /**
     * 获取后台菜单
     * @param string $rules
     * @return array|mixed|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenus($rules = '')
    {
        $menus = session('ADMIN_MENU_LIST');
        if (empty($menus)) {
            // 获取主菜单
            $map[] = ['hide', '=', 0];
            $map[] = ['status', '=', 1];
            $map[] = ['pid', '=', 0];
            //判断是否处于开发者模式下
            if (!config('DEVELOP_MODE')) {
                $map[] = ['is_dev', '=', 0];
            }
            if ($rules) {
                $map[] = ['id', 'in', $rules];
            }
            $menus = db('menu')->where($map)->order('sort asc')->select();


            foreach ($menus as $key => $item) {
                $map2 = array();
                $map2[] = ['hide', '=', 0];
                $map2[] = ['status', '=', 1];
                $map2[] = ['pid', '=', $item['id']];
                //判断是否处于开发者模式下
                if (!config('DEVELOP_MODE')) {
                    $map2[] = ['is_dev', '=', 0];
                }
                if ($rules) {
                    $map2[] = ['id', 'in', $rules];
                }

                $child = db('menu')->where($map2)->order('sort asc')->select();
                if ($child) {
                    $menus[$key]['child'] = $child;
                }
            }
            session('ADMIN_MENU_LIST', $menus);
        }

        return $menus;
    }

    /**
     * 获取用户组权限
     * @param $group_id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRules($group_id)
    {
        $authGroup = db('auth_group')->find($group_id);
        return $authGroup;
    }

    /**
     * 判断权限
     * @param $rules
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAuth($rules)
    {

        $request = request();
        $check = strtolower($request->controller() . '/' . $request->action());

        //首页
        if ($check == 'index/index') {
            return true;
        }
        //使用当前访问的url地址去数据库中检索
        $adminMenu = db('menu')->where('url', 'like', "$check%")->field('id')->find();

        //如果后台菜单中无记录，无权限访问。
        if (!$adminMenu) {
            return false;
        }
        $ruleArr = explode(',', $rules);
        if (!in_array($adminMenu['id'], $ruleArr)) {
            return false;
        }
        return true;
    }

    /**
     * 判断访问IP地址
     * @param $authGroup
     * @return bool
     */
    public function checkAllowIP($authGroup)
    {
        //allow_ip为空着默认为无IP限制
        if (empty($authGroup['allow_ip'])) {
            return true;
        }
        $allowIpArr = explode('|', $authGroup['allow_ip']);
        if (!in_array($this->request->ip(), $allowIpArr)) {
            return false;
        }
        return true;
    }


    /**
     * 退出登录
     */
    public function logout()
    {
        session('ADMIN_MENU_LIST', null);
        session('ADMIN_MEMBER_RULES', null);
        session('uid', null);
    }

    /**
     * 如果在非第一页没有数据时，跳转到最后一页
     * @param $lists
     */
    public function ifPageNoData($lists)
    {
        $currentPage = $lists->currentPage();
        $page = input('page/d');
        $page = $page ? $page : 1;
        if ($currentPage != $page) {
            //page是url传递的
            //$currentPage是程序生成的。超出数据分页数，$currentPage为最后一页页码
            $currentUrl = request()->url();
            $newUrl = preg_replace('/([\?\&])page=\d+$/', '$1' . "page=$currentPage", $currentUrl);
            header("Location: $newUrl");
            exit();
        }
    }
}