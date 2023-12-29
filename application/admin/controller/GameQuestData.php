<?php

namespace app\admin\controller;

use app\admin\model\GameQuestData as QuestDataModel;
use app\common\CsvManage;

class GameQuestData extends Base
{
    public function index()
    {
        $quest_id = trim(input('id'));
        if ($quest_id) {
            $where[] = ['id', '=', $quest_id];
        }
        $name = trim(input('name'));
        $where[] = ['1', '=', 1];
        if ($name) {
            $where[] = ['name', 'like', "%$name%"];
        }

        $lists = QuestDataModel::where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'name' => $name,
            'lists' => $lists,
            'page' => $page,
            'id' => $quest_id,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '任务信息列表'
        ]);
        return $this->fetch();
    }

    /**
     * 任务列表文件上传
     */
    public function upload()
    {
        if (request()->isPost()) {
            $path = '../public/upload/csv/';
            if (!file_exists($path)) {
                //默认的 mode 是 0777，意味着最大可能的访问权。
                mkdir($path, 0777, true);
            }
            $tmpname = $_FILES['propfile']['tmp_name'];
            $filename = $_FILES['propfile']['name'];

            $file = $path . '/' . $filename;

            if (empty($tmpname)) {
                $this->error('请选择上传文件');
            }

            if (empty($file)) {
                $this->error('请选择上传文件');
            }

            if (move_uploaded_file($tmpname, $file)) {
                $handle = fopen($file, 'r');

                $result = CsvManage::input_csv($handle); // 解析csv
                $len_result = count($result);
                if ($len_result == 0) {
                    $this->error('此文件中没有数据！');
                }
                $ret_arr = array();
                for ($i = 3; $i < $len_result + 3; $i++) {
                    // 循环获取各字段值
                    $arr = @array_values($result[$i]);
                    $data['id'] = $arr[0];
                    $data['name'] = mb_convert_encoding($arr[1], "UTF-8", "GBK");
                    $data['type'] = mb_convert_encoding($arr[2], "UTF-8", "GBK");
                    $data['mapId'] = $arr[3];
                    array_push($ret_arr, $data);
                }
                fclose($handle); // 关闭指针

                $res = db('game_quest_data')->where('1=1')->delete();
                if ($res !== false) {
                    // 批量插入数据表中
                    $result = QuestDataModel::insertAll($ret_arr);
                    if ($result) {
                        $resData = [
                            'code' => 1,
                            'msg' => '文件上传成功,数据已经导入,请重新刷新查看！',
                            'data' => '',
                            'url' => '/index',
                            'wait' => 2
                        ];
                        return json($resData);
                    } else {
                        $resData = [
                            'code' => 0,
                            'wait' => 2,
                            'data' => '',
                            'url' => '/index',
                            'msg' => '文件上传失败，请重新导入！'
                        ];
                        return json($resData);
                    }
                } else {
                    $this->error('任务清空失败！');
                }
            } else {
                $this->error('文件上传失败！');
            }
        } else {
            $this->assign(['meta_title' => '道具文件上传']);
            return $this->fetch();
        }
    }

    /**
     * 清空任务
     */
    public function clear()
    {
        $res = QuestDataModel::where('1=1')->delete();
        if ($res !== false) {
            $this->success('任务清空成功！', '');
        } else {
            $this->error('任务清空失败！');
        }
    }
}
