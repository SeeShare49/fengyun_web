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
        //->order(['status'=>'desc','action_value'=>'asc'])
        ->orderRaw('status DESC,CAST(action_value AS UNSIGNED) ASC')
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
    
    /**
     * 日志和日志类型文件上传
     */
    public function upload()
    {
        if (!request()->isPost())
        {
            $this->assign(['meta_title' => '日志和日志类型文件上传']);
            return $this->fetch();
        }
        $path = '../public/upload/csv/';
        if (!file_exists($path))
        {
         //默认的 mode 是 0777，意味着最大可能的访问权。
         mkdir($path, 0777, true);
        } 
        //获取上传文件
        $data = request()->file("propfile");
        if(!$data)
        {
            $this->error('请选择上传文件');
        }
        $tmpname = $data->getInfo()['tmp_name'];
        $filename = $data->getInfo()['name'];
        $file = $path . '/' . $filename;
        if (empty($tmpname))
        {
            $this->error('请选择上传文件');
        }
        if (empty($file))
        {
            $this->error('请选择上传文件');
        }
        if (!move_uploaded_file($tmpname, $file))
        {
            $this->error('文件上传失败！');
        } 
        // 正则解析日志和日志类型
        $handle = fopen($file, 'r');
        $result = fread($handle, filesize($file));
        //$result = CsvManage::input_csv($handle);
        preg_match_all('/\s*\/\/(.+)\s*([A-Z_]+)\s*=\s*([0-9]+);/',$result,$matches);
        if (!$matches || count($matches) != 4)
        {
            $this->error('此文件中没有正常格式数据！注：//说明/rLOG_TYPE = 10;');
        }
        //整理日志和日志类型参数数组
        $log_module_arr = array();
        $log_action_type_arr = array();
        $len_result = count($matches[0]);
        for($i = 0; $i < $len_result; $i++)
        {
            if(substr($matches[2][$i], 0, 15)  == 'LOG_ACTION_TYPE')
            {
                $log_action_type_arr[$i] = array($matches[3][$i], $matches[2][$i],$matches[1][$i]);
            }
            else if(substr($matches[2][$i], 0, 10) == 'LOG_MODULE')
            {
                $log_module_arr[$i] = array($matches[3][$i], $matches[2][$i],$matches[1][$i]);
            }
        }
        fclose($handle); // 关闭指针
        //print_r($log_action_type_arr);
       // print_r($log_module_arr);
        //日志参数入库处理
        if($log_module_arr)
        {
            //将所有数据状态改为0
            $res = \app\admin\model\GameLogAction::where([['status','=',1]])->update(['status'=>0]);
            if ($res !== false) 
            {
                foreach ($log_module_arr as $arr)
                {
                    //存在修改
                    if(\app\admin\model\GameLogAction::where(['action_name'=>$arr[1]])->find())
                    {
                        \app\admin\model\GameLogAction::where(['action_name'=>$arr[1]])->update(['status'=>1, 'action_value'=>$arr[0],'action_desc'=>$arr[2]]);
                    }
                    //不存在增加
                    else
                    {
                        \app\admin\model\GameLogAction::insert(['status'=>1,'action_name'=>$arr[1], 'action_value'=>$arr[0],'action_desc'=>$arr[2]]);
                    }
                }
            }
        }
        
        //日志类型参数入库处理
        if($log_action_type_arr)
        {
            //将所有数据状态改为0
            $res = \app\admin\model\GameLogActionType::where([['status','=',1]])->update(['status'=>0]);
            if ($res !== false) 
            {
                foreach ($log_action_type_arr as $arr)
                {
                    //存在修改
                    if(\app\admin\model\GameLogActionType::where(['action_type'=>$arr[1]])->find())
                    {
                        \app\admin\model\GameLogActionType::where(['action_type'=>$arr[1]])->update(['status'=>1, 'action_type_value'=>$arr[0],'action_type_desc'=>$arr[2]]);
                    }
                    //不存在增加
                    else
                    {
                        \app\admin\model\GameLogActionType::insert(['status'=>1,'action_type'=>$arr[1], 'action_type_value'=>$arr[0],'action_type_desc'=>$arr[2]]);
                    }
                }
            } 
        }
        
        $resData = [
            'code' => 1,
            'msg' => ',数据导入成功,LOG_ACTION_TYPE类型总数'.count($log_action_type_arr).',LOG_MODULE类型总数'.count($log_module_arr).'请重新刷新查看！',
            'data' => '',
            'url' => '/index',
            'wait' => 2
        ];
        return json($resData);
    }
}
