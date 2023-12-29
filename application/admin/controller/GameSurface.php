<?php

namespace app\admin\controller;

use app\admin\model\GameSurface as SurfaceModel;
use think\facade\Request;
use think\facade\View;

/**
 * 游戏界面列表
 **/
class GameSurface extends Base
{
    public function index($pid = 0)
    {
        $where[] = ['parent_id', '=', $pid];
        /** 游戏界面ID **/
        $surface_id = trim(input('surface_id'));
        if ($surface_id) {
            $where[] = ['surface_id', '=', $surface_id];
        }
        /** 界面类别（100：活动；101：充值；...） **/
        $surface_type = trim(input('surface_type'));
        if ($surface_type && $surface_type != -1) {
            $where[] = ['surface_type', '=', $surface_type];
        }
        /** 界面级别（1：一级界面；2、二级界面；...） **/
        $level = trim(input('level'));
        if ($level && $level != -1) {
            $where[] = ['level', '=', $level];
        }

        /** 界面标识（英文）/界面标识（中文） **/
        $symbol = trim(input('symbol'));
        if ($symbol) {
            $where[] = ['symbol_eng|symbol_chs', 'like', "%$symbol%"];
        }
        $status = trim(input('status'));
        if ($status) {
            $where[] = ['status', '=', $status];
        }

        $lists = SurfaceModel::field('id,surface_id,surface_type,symbol_eng,symbol_chs,symbol_eng,level,parent_id,status')->where($where)
            ->order('surface_type asc,level asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'symbol' => $symbol,
            'surface_type' => $surface_type,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '游戏界面展示列表'
        ]);
        return View::fetch();
    }

    /**
     * 创建游戏界面信息
     **/
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            //判断游戏界面ID是否存在
            $checkSurfaceId = SurfaceModel::where([['surface_id', '=', $data['surface_id']], ['surface_type', '=', $data['surface_type']]])->find();

            if ($checkSurfaceId) {
                $this->error('游戏界面类型【' . $data['surface_type'] . '】,界面ID【' . $data['surface_id'] . '】已存在！');
            }

            $ret = SurfaceModel::insertGetId($data);
            if ($ret) {
                action_log("surface_add", "game_surface", $ret, UID);
                $this->success('新增游戏界面信息成功', 'game_surface/index');
            } else {
                $this->error('新增游戏界面信息失败');
            }
        } else {
            $parent_id = Request::param('pid');
            $parent_type = Request::param('type');
            $parent_id = isset($parent_id) ? $parent_id : 0;
            $parent_lists = SurfaceModel::field('surface_id,surface_type,symbol_chs')->where([['parent_id', '=', 0], ['status', '=', 1]])->select();
            View::assign([
                'parent_id' => $parent_id,
                'parent_type' => $parent_type,
                'parent_lists' => $parent_lists,
                'meta_title' => '新增游戏界面信息'
            ]);
            return View::fetch();
        }
    }

    /**
     * 编辑游戏界面信息
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $info = SurfaceModel::find($id);
        if (!$info) {
            $this->error('游戏界面信息不存在或已删除！');
        }
        if (Request::isPost()) {
            $data = $_POST;

            //界面ID是否存在
            $checkInfo[] = ['surface_id', '=', $data['surface_id']];
            $checkInfo[] = ['surface_type', '=', $data['surface_type']];
            $checkInfo[] = ['id', '<>', $data['id']];
            $surfaceInfo = SurfaceModel::where($checkInfo)->find();
            if ($surfaceInfo) {
                $this->error('游戏界面类型【' . $data['surface_type'] . '】,界面ID【' . $data['surface_id'] . '】已存在！');
            }
            $ret = SurfaceModel::update($data);
            if ($ret) {
                //添加行为记录
                action_log("surface_edit", "surface_edit", $data['id'], UID);
                $this->success('游戏界面信息编辑成功', 'game_surface/index');
            } else {
                $this->error('游戏界面信息编辑失败');
            }
        } else {
            View::assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑游戏界面信息'
            ]);
            return View::fetch();
        }
    }


    /**
     * 删除游戏界面信息
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = SurfaceModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("surface_del", "action", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 启用禁用设置
     */
    public function set_surface_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            $surface_status = $data['status'] == true ? 'surface_open' : 'surface_close';
            $res = SurfaceModel::update($data);
            if ($res) {
                /** @var TYPE_NAME 添加行为记录 $action_status */
                action_log($surface_status, "game_surface", $data['id'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
