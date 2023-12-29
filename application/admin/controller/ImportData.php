<?php

namespace app\admin\controller;


use app\admin\model\PropCsv;
use app\common\CsvManage;

use Log;

class ImportData extends Base
{
    public function index()
    {
        $type_id = trim(input('type_id'));
        if ($type_id) {
            $where[] = ['type_id', '=', $type_id];
        }
        $name = trim(input('type_name'));
        $where[] = ['1', '=', 1];
        if ($name) {
            $where[] = ['type_name', 'like', "%$name%"];
        }

        $lists = PropCsv::where($where)
            ->order('type_id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'type_name' => $name,
            'lists' => $lists,
            'page' => $page,
            'type_id' => $type_id,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '道具信息列表'
        ]);
        return $this->fetch();
    }

    /**
     * 导入道具
     */
    public function import()
    {
        //$this->clear();
        $fileName = "../public/csv/itemdef.csv";
        $fp = fopen($fileName, "r");
        $data = fgetcsv($fp, 100, ",");
        $count = 1;
        // $result = array();
        while (!feof($fp) && $data = fgetcsv($fp)) {
            if ($count >= 3 && !empty($data)) {
                $row = eval('return ' . iconv('gbk', 'utf-8', var_export($data, true)) . ';');
                for ($i = 0; $i < count($data); $i++) {
                    //array_push($result, $data[$i]);
                    $info = array();
                    if ($i < 4) {
//                        $val = mb_convert_encoding($data[$i], "UTF-8", "GBK");
//                        array_push($result, iconv('gb2312', 'utf-8', $val));

                        $info['type_id'] = $data[0];
                        $info['sub_type'] = $data[1];
                        $info['icon_id'] = $data[2];
                        $info['type_name'] = mb_convert_encoding($data[3], "UTF-8", "GBK");
                        PropCsv::insert($info);
                    }
                    break;
                }
                //利用sql语句将文件内容存入数据库
                //print_r($result);
            }
            $count++;
        }
        fclose($fp);
        $this->success('道具导入成功！', '');
    }


    /**
     * 道具文件上传
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
                    $data['type_id'] = $arr[0];
                    $data['sub_type'] = $arr[1];

                    if (is_numeric($arr[2])) {
                        $data['type_name'] = mb_convert_encoding($arr[3], "UTF-8", "GBK");
                        $data['icon_id'] = $arr[2];
                    } else {
                        $data['icon_id'] = $arr[0];
                        $data['type_name'] = mb_convert_encoding($arr[2], "UTF-8", "GBK");
                    }
                    array_push($ret_arr, $data);
                }
                fclose($handle); // 关闭指针

                $res = db('prop_csv')->where('1=1')->delete();
                if ($res !== false) {
                    // 批量插入数据表中
                    $result = PropCsv::insertAll($ret_arr);
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
                        // 上传失败获取错误信息
                        // $this->error($file->getError());
                        $resData = [
//                            'code' => -1,
//                            'msg' => '文件上传失败，请重新导入！',
//                            'data' => '',
//                            'url' => '/index',
//                            'wait' => 2
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
            $this->assign(['meta_title' => '道具文件上传']);
            return $this->fetch();
        }
    }

    /**
     * 清空道具
     */
    public function clear()
    {
        $res = PropCsv::where('1=1')->delete();
        if ($res !== false) {
            $this->success('道具清空成功！', '');
        } else {
            $this->error('道具清空失败！');
        }
    }
}
