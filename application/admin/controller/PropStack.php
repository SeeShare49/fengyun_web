<?php

namespace app\admin\controller;

use app\common\CsvManage;

class PropStack extends Base
{
    public function index()
    {
        $prop_id = trim(input('prop_id'));
        if ($prop_id) {
            $where[] = ['prop_id', '=', $prop_id];
        }
        $name = trim(input('name'));
        $where[] = ['1', '=', 1];
        if ($name) {
            $where[] = ['name', 'like', "%$name%"];
        }

        $lists = \app\admin\model\PropStack::where($where)
            ->order('prop_id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'name' => $name,
            'prop_id' => $prop_id,
            'meta_title' => '道具堆加上限列表'
        ]);

        return $this->fetch();
    }


    /**
     * 道具堆加上限文件上传
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
                for ($i = 3; $i < $len_result + 3; $i++) { // 循环获取各字段值
                    $arr = @array_values($result[$i]);
                    $data['prop_id'] = $arr[0];
                    $data['name'] = mb_convert_encoding($arr[1], "UTF-8", "GBK");
                    $data['limit_num'] = $arr[2];

                    array_push($ret_arr, $data);
                }
                fclose($handle); // 关闭指针

                $res = db('prop_stack')->where('1=1')->delete();
                if ($res !== false) {
                    // 批量插入数据表中
                    $result = \app\admin\model\PropStack::insertAll($ret_arr);
                    if ($result) {
                        //$this->success('文件上传成功，数据已经导入！', 'index', 3);/**/
                        $resData = [
                            'code' => 1,
                            'msg' => '文件上传成功,数据已经导入,请重新刷新查看！'
                        ];
                        return json($resData);
                    } else {
                        // 上传失败获取错误信息
                        // $this->error($file->getError());
                        $resData = [
                            'code' => 0,
                            'msg' => '文件上传失败，请重新导入！'
                        ];
                        return json($resData);
                    }
                } else {
                    $this->error('道具清空失败！');
                }
            } else {
                $this->error('文件上传失败！');
            }
        } else {
            $this->assign(['meta_title' => '道具堆加上限文件上传']);
            return $this->fetch();
        }
    }

    /**
     * 清空道具
     */
    public function clear()
    {
        $res = \app\admin\model\PropStack::where('1=1')->delete();
        if ($res !== false) {
            $this->success('道具堆加上限列表清空成功！', '');
        } else {
            $this->error('道具堆加上限列表清空失败！');
        }
    }

    /**
     * 获取堆加上限数量
     * @param $id
     * @return \think\response\Json
     */
    public function get_limit_num($id)
    {
        if (isset($id)) {
            $info = db('prop_stack')->where('prop_id', '=', $id)->find();
            if ($info) {
                return json(['code' => 1, 'msg' => 'ok', 'data' => $info['limit_num']]);
            } else {
                return json(['code' => 1, 'msg' => '暂未设置上限堆加', 'data' => 9999999999999999999]);
            }
        } else {
            return json(['code' => -1, 'msg' => 'id参数错误', 'data' => 0]);
        }
    }
}
