<?php

namespace app\admin\controller;


use think\facade\Request;

class GameLogAction extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['status', '>', -1];
        if ($search) {
            $where[] = ['action_name|action_desc', 'like', "%$search%"];
        }
        $lists = \app\admin\model\GameLogAction::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'search'=>$search,
            'meta_title' => '游戏动作配置列表'
        ]);
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $gameActionValidate = new \app\admin\validate\GameLogAction();
            if (!$gameActionValidate->check($data)) {
                $this->error($gameActionValidate->getError());
            }

            //判断游戏动作配置名称是否重复
            $checkWhere[] = ['action_name', '=', $data['action_name']];
            $checkWhere[] = ['status', '>', -1];
            $checkActionName = \app\Admin\model\GameLogAction::where($checkWhere)->find();
            if ($checkActionName) {
                $this->error('游戏动作配置名重复！');
            }

            $data['status'] = 1;
            $re = \app\admin\model\GameLogAction::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("game_log_action_add", "game_log_action", $re, UID);
                $this->success('新增游戏动作配置成功', 'GameLogAction/index');
            } else {
                $this->error('新增游戏动作配置失败');
            }
        } else {
            $action_type_list = \app\common\GameLogActionType::getActionTypeList();
            $this->assign(
                [
                    'meta_title' => '添加游戏动作配置',
                    'action_type_list' => $action_type_list
                ]
            );
            return $this->fetch();
        }
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = \app\admin\model\GameLogAction::find($id);
        if (!$info) {
            $this->error('游戏动作配置不存在或已删除！');
        }
        if (Request::isPost()) {
            $data = $_POST;
            //验证
            $gameActionValidate = new \app\admin\validate\GameLogAction();
            if (!$gameActionValidate->check($data)) {
                $this->error($gameActionValidate->getError());
            }
            //判断行为标识是否重复

            $checkWhere[] = ['action_name', '=', $data['action_name']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            $checkName = \app\admin\model\GameLogAction::where($checkWhere)->find();
            if ($checkName) {
                $this->error('游戏动作配置标识重复！');
            }

            $re = \app\admin\model\GameLogAction::update($data);
            if ($re) {
                //添加行为记录
                action_log("game_log_action_edit", "game_log_action", $data['id'], UID);
                $this->success('编辑游戏动作配置成功', '');
            } else {
                $this->error('编辑游戏动作配置失败');
            }
        } else {
            $action_type_list = \app\common\GameLogActionType::getActionTypeList();
            $this->assign([
                'id' => $id,
                'info' => $info,
                'action_type_list' => $action_type_list,
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
        $ret = \app\admin\model\GameLogAction::where($where)->update($data);
        if ($ret) {
            action_log('game_log_action_del', 'game_log_action', $ids, UID);
            $this->success('删除游戏动作配置成功!', '/index');
        } else {
            $this->error('删除游戏动作配置失败!');
        }
    }

    public function set_type_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $type_status = "game_log_action_status_show";
            if ($data['status'] == 0) $type_status = "game_log_action_status_hide";

            $res = \app\admin\model\GameLogAction::update($data);
            if ($res) {
                /** 添加行为记录 @var TYPE_NAME $type_status */
                action_log($type_status, "game_log_action", $data['id'], UID);
                $this->success('游戏游戏动作配置状态修改成功！');
            } else {
                $this->error('游戏游戏动作配置状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
