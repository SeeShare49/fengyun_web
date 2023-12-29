<?php

namespace app\admin\controller;

use app\admin\model\PropCsv;
use app\admin\model\Welfare as WelfareModel;

use app\admin\validate\Welfare as WelfareValidate;

use think\facade\Log;
use think\facade\Request;

class Welfare extends Base
{
    /**
     * 福利配置中心列表
     * @return \think\Response
     */
    public function index()
    {
        $title = trim(input('title'));
        $where[] = ['status', '>', -1];
        if ($title) {
            $where[] = ['title', 'like', "%$title%"];
        }
        $lists = WelfareModel::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'title' => $title,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="12">暂无数据</td>',
            'meta_title' => '礼包列表列表',
        ]);
        return $this->fetch();
    }

    /**
     * 添加福利配置信息
     * @return \think\Response
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $welfareValidate = new WelfareValidate();
            if (!$welfareValidate->check($data)) {
                $this->error($welfareValidate->getError());
            }
            $newsData = array();
            $newsData['welfare_type'] = $data['welfare_type'];
            $newsData['title'] = $data['title'];
            $newsData['image_url'] = $data['image_url'];
            $newsData['get_rule'] = $data['get_rule'];
            $newsData['limit_time'] = strtotime($data['limit_time']);
            $newsData['cycle'] = $data['cycle'];
            $newsData['is_disposable'] = $data['is_disposable'];
            $newsData['status'] = 1;
            //清除上传文件的字段
            unset($data['file']);

            $prop_info = "";
            $items_desc = '';
            if (isset($data['item_id']) && isset($data['item_count']) && (intval($data['item_id']) > 0 && intval($data['item_count']) > 0)) {
                $prop_info .= $data['item_id'] . '|' . $data['item_count'];
                $items_desc .= get_prop_name($data['item_id']) . '|' . $data['item_count'];
            }
            if (isset($data['item_id_1']) && isset($data['item_count_1']) && (intval($data['item_id_1']) > 0 && intval($data['item_count_1']) > 0)) {
                $prop_info .= ';' . $data['item_id_1'] . '|' . $data['item_count_1'];
                $items_desc .= get_prop_name($data['item_id_1']) . '|' . $data['item_count_1'];
            }
            if (isset($data['item_id_2']) && isset($data['item_count_2']) && (intval($data['item_id_2']) > 0 && intval($data['item_count_2']) > 0)) {
                $prop_info .= ';' . $data['item_id_2'] . '|' . $data['item_count_2'];
                $items_desc .= get_prop_name($data['item_id_2']) . '|' . $data['item_count_2'];
            }
            if (isset($data['item_id_3']) && isset($data['item_count_3']) && (intval($data['item_id_3']) > 0 && intval($data['item_count_3']) > 0)) {
                $prop_info .= ';' . $data['item_id_3'] . '|' . $data['item_count_3'];
                $items_desc .= get_prop_name($data['item_id_3']) . '|' . $data['item_count_3'];
            }
            if (isset($data['item_id_4']) && isset($data['item_count_4']) && (intval($data['item_id_4']) > 0 && intval($data['item_count_4']) > 0)) {
                $prop_info .= ';' . $data['item_id_4'] . '|' . $data['item_count_4'];
                $items_desc .= get_prop_name($data['item_id_4']) . '|' . $data['item_count_4'];
            }
            $newsData['content'] = $prop_info;
            $newsData['items_desc'] = $items_desc;
            $re = WelfareModel::insertGetId($newsData);
            if ($re) {
                //添加行为记录
                action_log("welfare_add", "welfare", $re, UID);
                $this->success('福利礼包信息添加成功!', 'welfare/index');
            } else {
                $this->error("福利礼包信息添加失败!");
            }
        } else {
            //$prop_list = PropCsv::select();
            $type_id = trim(input('item_id'));
            $this->assign([
                //'prop_list' => $prop_list,
                'item_id' => $type_id,
                'meta_title' => '新增福利礼包信息'
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
        $info = WelfareModel::find($id);
        if (!$info) {
            $this->error("礼包信息不存在或已删除!");
        }

        if (Request::isPost()) {
            $data = $_POST;
            $welfareValidate = new WelfareValidate();
            if (!$welfareValidate->check($data)) {
                $this->error($welfareValidate->getError());
            }
            $newsData = array();
            $newsData['id'] = $data['id'];
            $newsData['welfare_type'] = $data['welfare_type'];
            $newsData['title'] = $data['title'];
            $newsData['image_url'] = $data['image_url'];
            //$newsData['content'] = $data['content'];
            $newsData['get_rule'] = $data['get_rule'];
            $newsData['limit_time'] = strtotime($data['limit_time']);
            $newsData['cycle'] = $data['cycle'];
            $newsData['is_disposable'] = $data['is_disposable'];
            $newsData['status'] = 1;
            //清除上传文件的字段
            unset($data['file']);

            $prop_info = '';
            if (isset($data['item_id_0']) && isset($data['item_count_0']) && (intval($data['item_id_0']) > 0 && intval($data['item_count_0']) > 0)) {
                $prop_info .= $data['item_id_0'] . '|' . $data['item_count_0'];
            }
            if (isset($data['item_id_1']) && isset($data['item_count_1']) && (intval($data['item_id_1']) > 0 && intval($data['item_count_1']) > 0)) {
                $prop_info .= ';' . $data['item_id_1'] . '|' . $data['item_count_1'];
            }
            if (isset($data['item_id_2']) && isset($data['item_count_2']) && (intval($data['item_id_2']) > 0 && intval($data['item_count_2']) > 0)) {
                $prop_info .= ';' . $data['item_id_2'] . '|' . $data['item_count_2'];
            }
            if (isset($data['item_id_3']) && isset($data['item_count_3']) && (intval($data['item_id_3']) > 0 && intval($data['item_count_3']) > 0)) {
                $prop_info .= ';' . $data['item_id_3'] . '|' . $data['item_count_3'];
            }
            if (isset($data['item_id_4']) && isset($data['item_count_4']) && (intval($data['item_id_4']) > 0 && intval($data['item_count_4']) > 0)) {
                $prop_info .= ';' . $data['item_id_4'] . '|' . $data['item_count_4'];
            }
            $newsData['content'] = $prop_info;


            $re = WelfareModel::update($newsData);
            if ($re) {
                action_log("welfare_edit", "welfare", $re, UID);
                $this->success('福利礼包信息编辑成功!', 'welfare/index');
            } else {
                $this->error("福利礼包信息编辑失败!");
            }

        } else {
            $item_id_0 = 0;
            $item_count_0 = 0;
            $item_id_1 = 0;
            $item_count_1 = 0;
            $item_id_2 = 0;
            $item_count_2 = 0;
            $item_id_3 = 0;
            $item_count_3 = 0;
            $item_id_4 = 0;
            $item_count_4 = 0;
            if (isset($info['content']) && !empty($info['content'])) {
                $items_list = explode(';', $info['content']);
                for ($i = 0; $i < count($items_list); $i++) {
                    $item = explode('|', $items_list[$i]);
                    if (count($item) == 2) {
                        switch ($i) {
                            case 0:
                                $item_id_0 = $item[0];
                                $item_count_0 = $item[1];
                                break;
                            case 1:
                                $item_id_1 = $item[0];
                                $item_count_1 = $item[1];
                                break;
                            case 2:
                                $item_id_2 = $item[0];
                                $item_count_2 = $item[1];
                                break;
                            case 3:
                                $item_id_3 = $item[0];
                                $item_count_3 = $item[1];
                                break;
                            default:
                                $item_id_4 = $item[0];
                                $item_count_4 = $item[1];
                                break;
                        }
                    }
                }
            }

            //$prop_list = PropCsv::select();
            $this->assign([
                'id' => $id,
                'info' => $info,
                'item_id_0' => $item_id_0,
                'item_count_0' => $item_count_0,
                'item_id_1' => $item_id_1,
                'item_count_1' => $item_count_1,
                'item_id_2' => $item_id_2,
                'item_count_2' => $item_count_2,
                'item_id_3' => $item_id_3,
                'item_count_3' => $item_count_3,
                'item_id_4' => $item_id_4,
                'item_count_4' => $item_count_4,
                //'prop_list' => $prop_list,
                'empty' => '<td class="empty" colspan="7">暂无数据</td>',
                'meta_title' => '编辑福利礼包信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除福利中心配置数据信息
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = WelfareModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("welfare_del", "welfare", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }


    /**
     * 福利礼包详情信息
     * @param $id
     **/
    public function detail($id)
    {
        $info = \app\admin\model\Welfare::find($id);
        if (empty($info)) {
            $this->error('福利礼包数据不存在或已删除!');
        }

        $prop_info = '';
        if (!empty($info['content'])) {
            $items_list = explode(';', $info['content']);
            for ($i = 0; $i < count($items_list); $i++) {
                $item = explode('|', $items_list[$i]);
                if (count($item) == 2) {
                    $prop_info .= get_prop_name(intval($item[0])) . '*' . $item[1] . PHP_EOL;
                }
            }
        }

        $this->assign([
            'info' => $info,
            'prop_info' => $prop_info,
            'meta_title' => '福利礼包详情信息'
        ]);
        return $this->fetch();
    }

    /**
     * 启用禁用福利礼包信息
     */
    public function set_status()
    {
        if (Request::isPost()) {
            $data['id'] = input('id');
            $data['status'] = input('val');

            $status = $data['status'] == true ? 'welfare_status_show' : 'welfare_status_hide';
            $res = WelfareModel::update($data);
            if ($res) {
                //添加行为记录
                /** @var TYPE_NAME $status */
                action_log($status, "welfare", $data['id'], UID);
                $this->success('福利礼包状态修改成功！');
            } else {
                $this->error('福利礼包状态修改失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }
}
