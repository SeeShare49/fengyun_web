<?php

namespace app\admin\controller;

use app\admin\validate\Menu as AdminMenuValidate;

use app\admin\model\Menu as MenuModel;
use think\facade\Request;

class Menu extends Base
{
    /**
     * 菜单列表
     * @param int $pid
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($pid = 0)
    {
        $title = trim(input('get.title'));
        $map[] = ['pid', '=', $pid];
        if ($title) {
            $map[] = ['title', 'like', "%" . $title . "%"];
        }

        $lists = MenuModel::where($map)->order('sort asc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $topMenu = ['pid' => 0, 'title' => '顶级分类'];
        if ($pid != 0) {
            $topMenu = MenuModel::find($pid);
            $topMenu = $topMenu;
        }
        $this->assign([
            'title' => $title,
            'topMenu' => $topMenu,
            'lists' => $lists,
            'pid' => $pid,
            'page' => $page,
            'meta_title' => '菜单列表'
        ]);
        return $this->fetch();
    }

    /**
     * 创建菜单
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $adminMenuValidate = new AdminMenuValidate();
            if (!$adminMenuValidate->check($data)) {
                $this->error($adminMenuValidate->getError());
            }
            $re = MenuModel::insertGetId($data);
            if ($re) {
                session('ADMIN_MENU_LIST', null);
                //添加行为记录
                action_log("menu_add", "menu", $re, UID);
                $this->success('新增成功', '');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign([
                'pid' => input('pid'),
                'meta_title' => '新增菜单'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 编辑菜单
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $info = MenuModel::find($id);
        if (!$info) {
            $this->error('后台菜单不存在或已删除！');
        }
        if (request()->isPost()) {
            $data = $_POST;
            $adminMenuValidate = new AdminMenuValidate();
            if (!$adminMenuValidate->check($data)) {
                $this->error($adminMenuValidate->getError());
            }
            $data['hide'] = isset($data['hide']) ? 1 : 0;
            $re = MenuModel::update($data);
            if ($re) {
                session('ADMIN_MENU_LIST', null);
                //添加行为记录
                action_log("menu_edit", "menu", $data['id'], UID);
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->assign([
                'id'=> $id,
                'info'=> $info,
                'meta_title'=>'编辑菜单'
            ]);

            return $this->fetch();
        }
    }

    /**
     * 删除指定菜单
     */
    public function del()
    {
        $ids = input('ids/a');
        //判断要删除的数据，是否有子菜单。
        foreach ($ids as $item) {
            $child = MenuModel::where('pid', $item)->find();
            if ($child) {
                $this->error('检测到要删除菜单下，存在子菜单。请删除子菜单后，再执行删除命令!');
                return;
            }
        }

        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        if (MenuModel::delete($ids)) {
            session('ADMIN_MENU_LIST', null);
            //添加行为记录
            action_log("menu_del", "menu", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 菜单排序
     */
    public function sort()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['sort'] = input('sort');

            $adminMenuValidate = new AdminMenuValidate();
            if (!$adminMenuValidate->scene('sort')->check($data)) {
                $this->error($adminMenuValidate->getError());
            }
            $res = MenuModel::update($data);
            if ($res) {
                session('ADMIN_MENU_LIST', null);
                //                添加行为记录
                action_log("menu_sort", "menu", $data['id'], UID);
                $this->success('排序修改成功！');
            } else {
                $this->error('排序修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 显示隐藏菜单
     */
    public function hide()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['hide'] = input('val');

            if ($data['hide'] == 1) {
                //隐藏
                $menu_status = "menu_status_hide";
            }
            if ($data['hide'] == 0) {
                //显示
                $menu_status = "menu_status_show";
            }

            $res = MenuModel::update($data);
            if ($res) {
                session('ADMIN_MENU_LIST', null);
                //添加行为记录
                action_log($menu_status, "menu", $data['id'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
