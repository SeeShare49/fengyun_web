<?php

namespace app\admin\controller;

use app\admin\validate\Config as ConfigValiate;

use app\admin\model\Config as ConfigModel;
use think\console\command\Lists;

class Config extends Base
{
    /**
     * 配置管理
     */
    public function index()
    {
        /* 查询条件初始化 */
        $map['status'] = array('gt', -1);

        $group = input('group', 0);

        $title = input('title', '');

        $lists = ConfigModel::where('status', '>', -1);
        if ($group) {
            $lists = $lists->where('group', $group);
        }
        if ($title) {
            $lists = $lists->where('name|title', 'like', "%$title%");
        }
        $lists = $lists->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'title' => $title,
            'group_id' => $group,
            'lists' => $lists,
            'page' => $page,
            'group' => config('CONFIG_GROUP_LIST'),
            'meta_title' => '配置管理'
        ]);
        return $this->fetch();
    }

    public function groups()
    {
        $id = input('id', 1);
        $lists = ConfigModel::where([
            ['status', '=', 1],
            ['group', '=', $id]
        ])->select();

        $this->assign([
            'id' => $id,
            'lists' => $lists,
            'group' => config('CONFIG_GROUP_LIST'),
            'meta_title' => '网站设置'
        ]);
        return $this->fetch();
    }


    /**
     * 创建站点配置
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $configValidate = new ConfigValiate();
            if (!$configValidate->check($data)) {
                $this->error($configValidate->getError());
            }
            //二次验证配置项或配置值
            $this->validateConfig($data['name'], $data['value']);
            //判断是否和数组库中的配置重复
            $config = ConfigModel::where('name', $data['name'])->find();
            if ($config) {
                $this->error('配置标识重复！');
            }
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            $re = ConfigModel::insertGetId($data);
            if ($re) {
                cache('DB_CONFIG_DATA_ADMIN', null);
                cache('DB_CONFIG_DATA_INDEX', null);
                //添加行为记录
                action_log("config_add", "config", $re, UID);
                $this->success('新增成功', '');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign([
                'pid' => input('pid'),
                'meta_title' => '新增设置'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 保存站点配置
     * @param $config
     */
    public function save($config)
    {
        if ($config && is_array($config)) {
            foreach ($config as $name => $value) {
                db('config')->where('name', $name)->setField('value', $value);
            }
        }
        cache('DB_CONFIG_DATA_ADMIN', null);
        cache('DB_CONFIG_DATA_INDEX', null);
        //添加行为记录
        action_log("config_set", "config", 0, UID);
        $this->success('保存成功！');
    }

    /**
     * 编辑站点配置
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $info = ConfigModel::find($id);
        if (!$info) {
            $this->error('配置不存在或已删除！');
        }
        if (request()->isPost()) {
            $data = $_POST;
            $configValidate = new ConfigValiate();
            if (!$configValidate->check($data)) {
                $this->error($configValidate->getError());
            }
            //二次验证配置项或配置值
            $this->validateConfig($data['name'], $data['value']);
            $data['update_time'] = time();
            $re = ConfigModel::update($data);
            if ($re) {
                cache('DB_CONFIG_DATA_ADMIN', null);
                cache('DB_CONFIG_DATA_INDEX', null);

                //添加行为记录
                action_log("config_edit", "config", $data['id'], UID);
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑站点配置'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除指定站点配置
     */
    public function delete()
    {
        $ids = input('ids/a');

        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        foreach ($ids as $value) {
            //网站基础配置 22个  不允许删除网站基础配置
            if ($value <= 22) {
                $this->error('不允许删除网站基础配置!');
            }
        }
        if (ConfigModel::delete($ids)) {
            cache('DB_CONFIG_DATA_ADMIN', null);
            cache('DB_CONFIG_DATA_INDEX', null);
            //添加行为记录
            action_log("config_del", "config", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 配置参数验证
     * @param $name
     * @param $value
     */
    private function validateConfig($name, $value)
    {
        switch ($name) {
            case 'WEB_REWRITE':
                //判断是否伪静态设置，如果是，清除栏目分类缓存
                cache('DATA_DOCUMENT_CATEGORY_LIST', null);
                break;
            case 'WEB_TEMPLATE_PATH':
                $match = preg_match('/^[0-9a-zA-Z]+$/', $value);
                if (!$match) {
                    $this->error('模板目录名必须以字母或数字命名！');
                }
                break;
        }
    }

    /**
     * 获取某个标签的配置参数
     */
    public function group()
    {
        $id = I('get.id', 1);
        $type = C('CONFIG_GROUP_LIST');
        $where[] = ['status', '=', 1];
        $where[] = ['group', '=', $id];
        $list = ConfigModel::where($where)->field('id,name,title,extra,value,remark,type')->order('sort')->select();
        if ($list) {
            $this->assign('list', $list);
        }
        $this->assign([
            'id' => $id,
            'meta_title' => $type[$id] . '设置'
        ]);
        $this->display();
    }
}
