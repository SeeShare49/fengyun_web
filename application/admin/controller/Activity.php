<?php

namespace app\admin\controller;

use app\common\TypeManage;

use app\common\ServerManage;

use app\admin\validate\Activity as ActivityValidate;

use app\admin\model\Activity as ActivityModel;

use think\facade\Request;

class Activity extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //服务器列表
        $serverlist = ServerManage::getServerList();
        //活动分类列表
        $typelist = TypeManage::getActivityTypeList();

        $name = trim(input('name'));
        $where[] = ['status', '>', -1];
        if ($name) {
            $where[] = ['name', 'like', "%$name%"];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        $type = trim(input('activity_type'));
        if ($type) {
            $where[] = ['activity_type', '=', $type];
        }

        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));

        $lists = ActivityModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'name' => $name,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'serverlist' => $serverlist,
            'typelist' => $typelist,
            'activity_type' => $type,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '活动信息列表'
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
            $activityValidate = new ActivityValidate();
            if (!$activityValidate->check($data)) {
                $this->error($activityValidate->getError());
            }

            $checkWhere[] = [
                ['name', '=', trim($data['name'])],
                ['status', '>', -1]
            ];

            $checkInfo = ActivityModel::where($checkWhere)->find();
            if ($checkInfo) {
                $this->error("活动名称:{$data['name']}已存在,请勿重复添加!");
            }

            $activity['name'] = $data['name'];
            $activity['server_id'] = $data['server_id'];
            $activity['activity_type'] = $data['activity_type'];
            $activity['task_id'] = $data['task_id'];
            $activity['target_id'] = $data['target_id'];
            $activity['active_cycle'] = $data['active_cycle'];
            $activity['start_time'] = $data['start_time'];
            $activity['end_time'] = $data['end_time'];
            $activity['sort'] = $data['sort'];
            $activity['status'] = $data['status'];
            $activity['create_time'] = $_SERVER['REQUEST_TIME'];

            $re = ActivityModel::insertGetId($activity);
            if ($re) {
                action_log("activity_add", "activity", $re, UID);
                $this->success("活动信息添加成功!", "activity/index");
            }
        } else {
            //初始化服务器列表
            $server_list = ServerManage::getServerList();
            //初始化活动类型
            $type_list = TypeManage::getActivityTypeList();
            //初始化任务类型
            $task_list = TypeManage::getTaskList();
            //初始化活动目标类型
            $target_list = TypeManage::getTargetList();
            //服务器ID
            $server_id = trim(input('server_id'));
            //活动类型
            $activity_type = trim(input('activity_type'));
            //任务ID
            $task_id = trim(input('task_id'));
            //目标ID
            $target_id = trim(input('target_id'));

            $this->assign([
                'server_list' => $server_list,
                'type_list' => $type_list,
                'task_list' => $task_list,
                'target_list' => $target_list,
                'server_id' => $server_id,
                'activity_type' => $activity_type,
                'task_id' => $task_id,
                'target_id' => $target_id,
                'meta_title' => '添加活动信息'
            ]);
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
        $info = ActivityModel::find($id);
        if (!$info) {
            $this->error("该活动信息不存在或已删除!");
        }

        if (request()->isPost()) {
            $data = $_POST;
            $activityValidate = new ActivityValidate();
            if (!$activityValidate->check($data)) {
                $this->error($activityValidate->getError());
            }
            $checkWhere[] = [
                ['name', '=', trim($data['name'])],
                ['status', '>', -1],
                ['id', '<>', $data['id']]
            ];
            $checkInfo = ActivityModel::where($checkWhere)->find();
            if ($checkInfo) {
                $this->error("活动名称:{$data['name']}已存在,请重新输入活动名称!");
            }

            $activity['id'] = $data['id'];
            $activity['name'] = $data['name'];
            $activity['server_id'] = $data['server_id'];
            $activity['activity_type'] = $data['activity_type'];
            $activity['task_id'] = $data['task_id'];
            $activity['target_id'] = $data['target_id'];
            $activity['active_cycle'] = $data['active_cycle'];
            $activity['start_time'] = strtotime($data['start_time']);
            $activity['end_time'] = strtotime($data['end_time']);
            $activity['sort'] = $data['sort'];
            $activity['status'] = $data['status'];
            $activity['desc'] = $data['desc'];
            $activity['update_time'] = $_SERVER['REQUEST_TIME'];

            $re = ActivityModel::update($activity);
            if ($re) {
                action_log("activity_edit", "activity", $re, UID);
                $this->success("活动信息编辑成功!", "activity/index");
            } else {
                $this->error("活动信息编辑失败!");
            }
        } else {
            /** 服务器列表 **/
            $server_list = ServerManage::getServerList();
            /** 活动类型列表 **/
            $type_list = TypeManage::getActivityTypeList();
            /** 任务列表 **/
            $task_list = TypeManage::getTaskList();
            /** 初始化活动目标类型 **/
            $target_list = TypeManage::getTargetList();
            /** 服务器ID **/
            $server_id = trim(input('server_id'));

            $this->assign([
                'id' => $id,
                'server_id' => $server_id,
                'server_list' => $server_list,
                'type_list' => $type_list,
                'task_list' => $task_list,
                'target_list' => $target_list,
                'info' => $info,
                'meta_title' => '编辑活动信息'
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
        $res = db('activity')->where($where)->update($data);
        if ($res) {
            /** 删除行为记录 @var  TYPE_NAME */
            action_log("activity_del", "activity", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }


    /**
     * 显示/隐藏活动信息
     */
    public function set_type_status()
    {
        if (request()->isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');
            if ($data['status'] == 1) {
                $activity_status = "activity_status_show";
            }
            if ($data['status'] == 0) {
                $activity_status = "activity_status_hide";
            }

            $res = db('activity')->update($data);
            if ($res) {
                /** 添加行为记录 @var  TYPE_NAME $activity_status */
                action_log($activity_status, "activity", $data['id'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
