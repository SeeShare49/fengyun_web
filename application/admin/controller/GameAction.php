<?php

namespace app\admin\controller;

use app\admin\validate\GameAction as GameActionValidate;

use app\admin\model\GameAction as GameActionModel;

class GameAction extends Base
{
    /**
     * 游戏动作配置列表
     */
    public function index()
    {
        $name = trim(input('name'));
        $where[] = ['status', '>', -1];
        if ($name) {
            $where[] = ['title', 'like', "%$name%"];
        }
        $lists = GameActionModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '游戏动作配置列表'
        ]);
        return $this->fetch();
    }

    /**
     * 添加动作配置
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $gameActionValidate = new gameActionValidate();
            if (!$gameActionValidate->check($data)) {
                $this->error($gameActionValidate->getError());
            }

            //判断游戏动作配置名称是否重复
            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkActionName = GameActionModel::where($checkWhere)->find();
            if ($checkActionName) {
                $this->error('游戏动作配置名重复！');
            }

//            //判断游戏动作配置标题是否重复
//            $checkTitle = db('game_action')
//                ->where('title', $data['title'])
//                ->where('status', '>', -1)
//                ->find();
//            if ($checkTitle) {
//                $this->error('游戏动作配置标题重复！');
//            }

            $data['status'] = 0;
            $data['update_time'] = time();
            $re = GameActionModel::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("game_action_add", "game_action", $re, UID);
                $this->success('新增成功', 'game_action/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign([
                'meta_title' => '添加游戏动作配置'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑游戏动作配置
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
        $info = GameActionModel::find($id);
        if (!$info) {
            $this->error('游戏动作配置不存在或已删除！');
        }
        if (request()->isPost()) {
            $data = $_POST;
            //验证
            $gameActionValidate = new GameActionValidate();
            if (!$gameActionValidate->check($data)) {
                $this->error($gameActionValidate->getError());
            }
            //判断行为标识是否重复

            $checkWhere[] = ['name', '=', $data['name']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            $checkName = GameActionModel::where($checkWhere)->find();
            if ($checkName) {
                $this->error('游戏动作配置标识重复！');
            }
//            //判断行为名称是否重复
//            $checkTwhere[] = ['title', '=', $data['title']];
//            $checkTwhere[] = ['status', '>', -1];
//            $checkTwhere[] = ['id', '<>', $data['id']];
//            $checkTitle = db('action')->where($checkTwhere)->find();
//            if ($checkTitle) {
//                $this->error('行为名称重复！');
//            }
            $data['update_time'] = time();
            $re = GameActionModel::update($data);
            if ($re) {
                //添加行为记录
                action_log("game_action_edit", "game_action", $data['id'], UID);
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑游戏动作配置'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请至少选择一项操作的数据!');
        }

        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $ret = GameActionModel::where($where)->update($data);
        if ($ret) {
            action_log('game_action_del', 'game_action', $ids, UID);
            $this->success('游戏行为删除成功!', 'index');
        } else {
            $this->error('游戏行为删除失败!');
        }
    }
}
