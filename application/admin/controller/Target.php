<?php

namespace app\admin\controller;

use app\common\TypeManage;

use app\admin\validate\Target as TargetValidate;

use app\admin\model\Target as TargetModel;


class Target extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $title = trim(input('name'));
        $where[] = ['status', '>', -1];
        if ($title) {
            $where[] = ['name', 'like', "%$title%"];
        }

        $lists = TargetModel::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'name'=>$title,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '活动目标列表'
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
        if (request()->isPost()) {
            $data = $_POST;
            $targetValidate = new TargetValidate();
            if (!$targetValidate->check($data)) {
                $this->error($targetValidate->getError());
            }

            $checkWhere[] = [
                ['target_type_id', '=', $data['target_type_id']],
                ['name', '=', trim($data['name'])],
                ['status', '>', -1]
            ];

            $info = TargetModel::where($checkWhere)->find();

            if ($info) {
                $this->error("目标类型:【{$data['target_type_id']}】,目标名称:【{$data['name']}】已存在请勿重复添加!");
            }

            $data['create_time'] = $_SERVER['REQUEST_TIME'];// time();
            $data['update_time'] = 0;
            $ret = TargetModel::insert($data);
            if ($ret) {
                action_log('target_add', 'target', $ret, UID);
                $this->success("目标类型:【{$data['target_type_id']}】,目标名称:【{$data['name']}】添加成功!");
            } else {
                $this->error("目标类型:【{$data['target_type_id']}】,目标名称:【{$data['name']}】添加失败!");
            }
        } else {
            $type_list = TypeManage::getTargetList();
            $this->assign([
                'typelist', $type_list,
                'target_type_id' => input('target_type_id'),
                'meta_title' => '添加目标奖励'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $info = TargetModel::find($id);
        if (!$info) {
            $this->error("数据不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $targetValidate = new TargetValidate();
            if (!$targetValidate->check($data))
                $this->error($targetValidate->getError());

            $checkWhere[] = [
                ['name', $data['name']],
                ['target_type_id', $data['target_type_id']],
                ['id', '<>', $data['id']],
                ['status', '>', -1]
            ];

            $checkInfo = TargetModel::where($checkWhere)->find();

            if ($checkInfo) {
                $this->error("目标类型:【{$data['target_type_id']}】,目标名称:【{$data['name']}】已存在请重新输入!");
            }

            $data['update'] = time();
            $ret = TargetModel::update($data);
            if ($ret) {
                action_log('target_edit', 'target', $ret, UID);
                $this->success("目标信息编辑成功!");
            } else {
                $this->error("目标信息编辑失败!");
            }

        } else {
            $type_list = TypeManage::getTargetList();
            $this->assign([
                'id'=>$id,
                'info'=>$info,
                'typelist' => $type_list,
                'meta_title' => '编辑目标信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * ajax post请求编辑目标奖励
     * @param $id
     * @param $type_id
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function ajaxEdit($id, $type_id)
    {
        $info = db("target")->find($id);
        if (!$info) {
            $resData = [
                'code' => -1,
                'msg' => '数据不存在或已删除!'
            ];
            return json($resData);
        }

        if (request()->isPost()) {
            $data = $_POST;
            $typeValidate = new \app\admin\model\Target();
            if (!$typeValidate->scene('ajaxEdit')->check($data)) {
                $resData = [
                    'code' => -1,
                    'msg' => $typeValidate->getError()
                ];
                return json($resData);
            }

            $checkWhere[] = [
                ['name', '=', $data['name']],
                ['target_type_id', '=', $data['target_type_id']],
                ['id', '<>', $data['id']],
                ['status', '>', -1]
            ];

            $checkInfo = db("target")
                ->where($checkWhere)
                ->find();

            if ($checkInfo) {
                $this->error("目标类型:【{$data['target_type_id']}】,目标名称:【{$data['name']}】已存在请重新输入!");
            }
            $datas['id'] = $data['id'];
            $datas['quota'] = $data['quota'];
            $datas['reward'] = $data['reward'];
            $datas['update_time'] = time();
            $ret = db("target")->update($datas);
            $list = $this->getTargetList($type_id);
            if ($ret) {
                action_log('target_edit', 'target', $ret, UID);
                $resData = [
                    'data' => $list,
                    'code' => 0,
                    'msg' => '目标信息编辑成功!'
                ];
                return json($resData);
            } else {
                $resData = [
                    'data' => $list,
                    'code' => -1,
                    'msg' => '目标信息编辑失败!'
                ];
                return json($resData);
            }
        } else {
            $type_list = TypeManage::getTargetList();
            $this->assign([
                'id' => $id,
                'info' => $info,
                'typelist' => $type_list,
                'meta_title' => '编辑目标信息'
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
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = db('target')->where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("task_del", "target", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * ajax post请求移除目标奖励
     * @param $id
     * @param $type_id
     */
    public function ajaxDel($id, $type_id)
    {
        $where[] = ['id', '=', $id];
        $data['status'] = -1;
        $res = TargetModel::where($where)->update($data);

        $list = $this->getTargetList($type_id);

        if ($res) {
            //添加行为记录
            action_log("task_del", "target", $id, UID);
            $resData = [
                'data' => $list,
                'code' => 0,
                'msg' => '目标奖励移除成功!'
            ];
            return json($resData);
        } else {
            $resData = [
                'data' => $list,
                'code' => -1,
                'msg' => '目标奖励移除失败!'
            ];
            return json($resData);
        }
    }

    /**
     * ajax post请求添加目标奖励
     */
    public function ajaxSave()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $typeValidate = new \app\admin\validate\Target();
            if (!$typeValidate->scene('ajaxSave')->check($data)) {
                $this->error($typeValidate->getError());
            }

            //            $checkWhere[] = [
            //                ['target_type_id', '=', $data['target_id']],
            //                ['status', '>', -1]
            //            ];
            //
            //            $info = db("target")
            //                ->where($checkWhere)
            //                ->find();
            //
            //            if ($info) {
            //                $this->error("目标类型:【{$data['target_type_id']}】已存在请勿重复添加!");
            //            }
            $data['status'] = 1;
            $data['desc'] = "目标额度:" . $data['quota'] . ",奖励道具:" . $data['reward'];
            $data['create_time'] = $_SERVER['REQUEST_TIME'];// time();
            $data['update_time'] = 0;
            $ret = TargetModel::insert($data);
            $list = $this->getTargetList($data['target_type_id']);

            if ($ret) {
                action_log('target_add', 'target', $ret, UID);
                $resData = [
                    'data' => $list,
                    'code' => 0,
                    'msg' => '目标奖励添加成功!'
                ];
                return json($resData);
            } else {
                $resData = [
                    'data' => $list,
                    'code' => -1,
                    'msg' => '目标奖励添加失败!'
                ];
                return json($resData);
            }
        }
    }

    /**
     * 根据target_type_id 获取目标信息
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTargetList($id)
    {
        $list = array();
        if (isset($id) && $id > 0) {
            $where[] = [
                ['target_type_id', '=', $id],
                ['status', '>', -1]
            ];
            $list = TargetModel::where($where)->select();
        }
        return $list;
    }
}
