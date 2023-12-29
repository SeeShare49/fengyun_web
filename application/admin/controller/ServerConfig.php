<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\facade\View;
use think\Request;

class ServerConfig extends Base
{
    /**
     * 数据库服务器配置列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $lists = \app\admin\model\ServerConfig::order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => \think\facade\Request::param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        View::assign([
            'page' => $page,
            'lists' => $lists,
            'meta_title' => '数据库服务器配置信息'
        ]);
        return View::fetch();
    }

    /**
     * 新增数据库服务器配置信息.
     *
     * @return \think\Response
     */
    public function create()
    {
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $valConfig = new \app\admin\validate\ServerConfig();
            if (!$valConfig->check($data)) {
                $this->error($valConfig->getError());
            }

            $ret = \app\admin\model\ServerConfig::insert($data);
            if ($ret) {
                action_log('server_config_add', 'server_config', $ret, UID);
                $this->success('数据库服务器配置信息添加成功!', 'server_config/index');
            } else {
                $this->error('数据库服务器配置信息添加失败!');
            }
        } else {
            View::assign([
                'meta_title' => '新增数据库服务器配置'
            ]);
            return View::fetch();
        }
    }


    /**
     * 编辑数据库服务器配置.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = \app\admin\model\ServerConfig::find($id);
        if (empty($info)) {
            $this->error('配置信息不存在或已删除!');
        }
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $valConfig = new \app\admin\validate\ServerConfig();
            if (!$valConfig->check($data)) {
                $this->error($valConfig->getError());
            }

            $ret = \app\admin\model\ServerConfig::update($data);
            if ($ret) {
                action_log('server_config_edit', 'server_config', $ret, UID);
                $this->success('数据库服务器配置修改成功!', 'server_config/index');
            } else {
                $this->error('数据库服务器配置修改失败,请重试！');
            }
        } else {
            View::assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑数据库服务器配置'
            ]);
            return View::fetch();
        }
    }


    /**
     * 删除数据库服务器配置信息
     *
     * @return \think\Response
     */
    public function delete()
    {
        $ids = trim(input('ids/a'));
        if (empty($ids)) {
            $this->error('请选择待删除的配置数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $ret = \app\admin\model\ServerConfig::update($data);
        if ($ret) {
            action_log('server_config_del', 'server_config', $ids, UID);
            $this->success('数据库服务器配置删除成功!');
        } else {
            $this->error('数据库服务器配置删除失败!');
        }
        return View::fetch();
    }
}
