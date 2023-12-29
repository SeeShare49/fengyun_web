<?php

namespace app\admin\controller;

use app\admin\model\UserInfo as UserInfoModel;
use think\facade\View;

/**
 * 白名单账号相关操作
 **/
class WhiteUser extends Base
{
    /**
     * 白名单用户列表
     */
    public function index()
    {
        $where[] = ['gm', '=', 1];
        $username = trim(input('userName'));
        $user_id = trim(input('userId'));

        if ($username) {
            $where[] = ['UserName', 'like', "%$username%"];
        }

        if ($user_id) {
            $where[] = ['UserID', '=', $user_id];
        }

        $lists = UserInfoModel::where($where)
            ->order('UserID desc,RegisterTime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'UserName' => $username,
            'user_id' => $user_id,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '游戏白名单用戶列表'
        ]);
        return $this->fetch();
        return View::fetch();
    }

    /**
     * 添加白名单用户
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;

            $info = \app\admin\model\UserInfo::where('UserID', '=', $data['userId']);
            if ($info) {

            }
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
     * 删除白名单用户
     * @param $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function del($id)
    {
        $res = \app\admin\model\WhiteUser::where(['userId', '=', $id])->delete(true);
        if ($res) {
            //添加行为记录
            action_log("white_user_del", "white_user", $id, UID);
            $this->success('白名单用户信息删除成功');
        } else {
            $this->error('白名单用户信息删除失败！');
        }
    }
}
