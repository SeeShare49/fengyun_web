<?php

namespace app\admin\controller;


class GameNotice extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $title = trim(input('title'));
        $where[] = ['status', '>', -1];
        if ($title) {
            $where[] = ['title', 'like', "%$title%"];
        }

        $this->assign('title', $title);
        $lists = \app\admin\model\GameNotice::where($where)
            ->order('is_top desc,id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '游戏公告信息'
        ]);
        return $this->fetch();
    }


    /**
     * 创建公告信息
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $noticeValidate = new \app\admin\validate\GameNotice();
            if (!$noticeValidate->check($data)) {
                $this->error($noticeValidate->getError());
            }
            //清除上传文件的字段
            unset($data['file']);
            $data['create_time'] = time();
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $re = \app\admin\model\GameNotice::insertGetId($data);
            if ($re) {
                //添加行为记录
                action_log("game_notice_add", "game_notice", $re, UID);
                $this->success('游戏公告信息添加成功!', 'game_notice/index');
            } else {
                $this->error("游戏公告信息添加失败!");
            }
        } else {
            $this->assign(['meta_title' => '创建游戏公告信息']);
            return $this->fetch();
        }
    }

    /**
     * 编辑公告信息
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
        $info = \app\admin\model\GameNotice::find($id);
        if (!$info) {
            $this->error("游戏公告信息不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $noticeValidate = new \app\admin\validate\GameNotice();
            if (!$noticeValidate->check($data)) {
                $this->error($noticeValidate->getError());
            }
            //清除上传文件的字段
            unset($data['file']);
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['update_time'] = time();
            $re = \app\admin\model\GameNotice::update($data);
            if ($re) {
                action_log("game_notice_edit", "game_notice", $re, UID);
                $this->success('游戏公告信息编辑成功!', 'game_notice/index');
            } else {
                $this->error("游戏公告信息编辑失败!");
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑游戏公告信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定资源
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = \app\admin\model\GameNotice::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("game_notice_del", "game_notice", $ids, UID);
            $this->success('游戏公告信息删除成功');
        } else {
            $this->error('游戏公告信息删除失败！');
        }
    }

    /**
     * 启用禁用公告信息
     */
    public function set_notice_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) $notice_status = "game_notice_status_show";
            if ($data['status'] == 0) $notice_status = "game_notice_status_hide";

            $res = \app\admin\model\GameNotice::update($data);
            if ($res) {
                //添加行为记录
                /** @var TYPE_NAME $notice_status */
                action_log($notice_status, "game_notice", $data['id'], UID);
                $this->success('游戏公告状态修改成功！');
            } else {
                $this->error('游戏公告状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 公告是否置顶设置
     **/
    public function set_notice_top()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['is_top'] = input('val');
            if ($data['is_top'] == 1) $notice_top = "is_top_true";
            if ($data['is_top'] == 0) $notice_top = "is_top_false";

            $res = \app\admin\model\GameNotice::update($data);
            if ($res) {
                //添加行为记录
                /** @var TYPE_NAME $notice_top */
                action_log($notice_top, "game_notice", $data['id'], UID);
                $this->success('游戏公告设置是否置顶成功！');
            } else {
                $this->error('游戏公告设置是否置顶失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
