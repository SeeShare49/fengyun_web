<?php

namespace app\admin\controller;

use app\admin\model\GameLogActionType as TypeModel;
use app\admin\validate\GameLogActionType as TypeValidate;
use think\facade\Request;

class GameLogActionType extends Base
{
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['status', '>', -1];
        if ($search) {
            $where[] = ['action_type|action_type_desc', 'like', "%$search%"];
        }

        $lists = TypeModel::where($where)
        //->order(['status'=>'desc','action_type_value'=>'asc'])
        ->orderRaw('status DESC,CAST(action_type_value AS UNSIGNED) ASC')
        ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'search' => $search,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '活动类别列表'
        ]);
        return $this->fetch();
    }

    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $typeValidate = new TypeValidate();
            if (!$typeValidate->check($data)) {
                $this->error($typeValidate->getError());
            }
            $checkWehre[] = ['action_type', '=', $data['action_type']];
            $checkWehre[] = ['status', '>', -1];
            $checkName = TypeModel::where($checkWehre)->find();

            if ($checkName) {
                $this->error("{$data['action_type']}游戏日志类型已存在,请勿重复添加!");
            }

            $re = TypeModel::insertGetId($data);
            if ($re) {
                action_log('game_log_action_type_add', 'game_log_action_type', $re, UID);
                $this->success("{$data['action_type']}游戏日志类型添加成功!", 'game_log_action_type/index');
            } else {
                $this->error("{$data['action_type']}游戏日志类型添加失败!");
            }
        } else {
            $this->assign(['meta_title' => '添加游戏日志类型']);
            return $this->fetch();
        }
    }

    public function edit($id)
    {
        $info = \app\admin\model\GameLogActionType::find($id);
        if (!$info) {
            $this->error("游戏日志类型不存在或已删除!");
        }

        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $typeValidate = new TypeValidate();
            if (!$typeValidate->check($data)) {
                $this->error($typeValidate->getError());
            }
            $checkWhere[] = ['action_type', '=', $data['action_type']];
            $checkWhere[] = ['status', '>', -1];
            $checkWhere[] = ['id', '<>', $data['id']];
            $checkName = db('game_log_action_type')
                ->where($checkWhere)
                ->find();
            if ($checkName) {
                $this->error("{$data['action_type']}游戏日志类型已存在,请重新编辑类型名称!");
            }

            $re = TypeModel::update($data);
            if ($re) {
                action_log('game_log_action_type_edit', 'game_log_action_type', $re, UID);
                $this->success("{$data['action_type']}游戏日志类型编辑成功!", "game_log_action_type/index");
            } else {
                $this->error("{$data['action_type']}游戏日志类型编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑游戏日志类型'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除游戏日志类型
    */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = TypeModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("game_log_action_type_del", "game_log_action_type", $ids, UID);
            $this->success('删除游戏日志类型成功');
        } else {
            $this->error('删除游戏日志类型失败！');
        }
    }

    /**
     * 设置游戏日志类型开启/关闭状态
    */
    public function set_type_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $type_status = "glc_type_status_show";
            if ($data['status'] == 0) $type_status = "glc_type_status_hide";

            $res = TypeModel::update($data);
            if ($res) {
                /** 添加行为记录 @var TYPE_NAME $type_status */
                action_log($type_status, "game_log_action_type", $data['id'], UID);
                $this->success('游戏日志类型状态修改成功！');
            } else {
                $this->error('游戏日志类型状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
