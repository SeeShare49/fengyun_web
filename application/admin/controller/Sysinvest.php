<?php

namespace app\admin\controller;

use app\admin\model\PropCsv;
use app\admin\model\SysProp;
use app\common\CsvManage;
use app\common\ServerManage;
use think\Db;
use app\admin\validate\Sysinvest as SysinvestValidate;
use Session;


use app\common;
use think\facade\Log;

class Sysinvest extends Base
{
    /**
     * 系统充值列表
     */
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $player_name = trim(input('player_name'));
        $where[] = ['1', '=', 1];
        if ($player_name) {
            $where[] = ['player_name', 'like', "%$player_name%"];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        $add_date = trim(input('add_date'));
        if ($add_date) {
            $start = strtotime($add_date . " " . "00:00:00");
            $end = strtotime($add_date . " " . "23:59:59");
            $where[] = ['create_time', 'between', [$start, $end]];
        }


        //统计系统充值总额
        $total_ingot = 0;//总系统充值元宝
        $total_gold = 0;//总系统充值金币
        $total_silver = 0; //总系统充值银票
        $rechargeInfo = \app\admin\model\SysInvest::field('sum(ingot) as total_ingot,sum(gold) as total_gold,sum(silver) as total_silver')
            ->where($where)
            ->find();
        if ($rechargeInfo) {
            $total_ingot = $rechargeInfo['total_ingot'];
            $total_gold = $rechargeInfo['total_gold'];
            $total_silver = $rechargeInfo['total_silver'];
        }


        $lists = \app\admin\model\SysInvest::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'server_list' => $server_list,
            'player_name' => $player_name,
            'server_id' => $server_id,
            'lists' => $lists,
            'page' => $page,
            'add_date' => $add_date,
            'total_ingot' => isset($total_ingot) ? $total_ingot : 0,
            'total_gold' => isset($total_gold) ? $total_gold : 0,
            'total_silver' => isset($total_silver) ? $total_silver : 0,
            'empty' => '<td class="empty" colspan="9">暂无数据</td>',
            'meta_title' => '系统充值记录列表'
        ]);
        return $this->fetch();
    }

    /**
     * 新增充值
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;
            $investValidate = new SysinvestValidate();
            if (!$investValidate->check($data)) {
                $this->error($investValidate->getError());
            }
            if ($data['server_id'] == 0) {
                return json(['code' => 0, 'msg' => '请选择服务器']);
            }

            sys_invest_record($data['server_id'], $data['player_name'], $data['yuan_bao'], $data['gold'], $data['diamonds']);
            common\test::webw_packet_add_money($data['server_id'], $data['player_name'], $data['yuan_bao'], $data['gold'], $data['diamonds']);

            return json(['code' => 1, 'msg' => '系统充值请求提交成功,待服务器处理......', 'data' => "norefresh"]);
        } else {
            $server_list = ServerManage::getServerList();
            $server_id = trim(input('server_id'));

            $this->assign([
                'server_list' => $server_list,
                'server_id' => $server_id,
                'meta_title' => '系统充值'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 批量扶持发放
     */
    public function batch_upload()
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

                $result = CsvManage::invest_input_csv($handle); // 解析csv
                $len_result = count($result);
                if ($len_result == 0) {
                    $this->error('此文件中没有数据！');
                }
                $ret_arr = array();
                for ($i = 1; $i < $len_result + 1; $i++) {
                    //setlocale(LC_ALL, 'zh_CN');
                    // 循环获取各字段值
                    Log::write("result返回数组列数:" . count($result[$i]));
                    $arr = @array_values($result[$i]);
                    Log::write("数组列数:" . count($arr));
                    $data['server_id'] = $arr[0];

                    $data['player_name'] = mb_convert_encoding($arr[1], "UTF-8", "GBK");
                    $data['ingot'] = $arr[2];
                    $data['gold'] = $arr[3];
                    if (!isset($arr[4])) {
                        $data['silver'] = 0;
                    } else {
                        $data['silver'] = $arr[4];
                    }
                    $data['admin_id'] = UID;
                    $data['ip'] = request()->ip();
                    $data['create_time'] = time();

                    array_push($ret_arr, $data);
                }
                fclose($handle); // 关闭指针

                // 批量插入数据表中
                $result = \app\admin\model\SysInvest::insertAll($ret_arr);
                if ($result) {
                    foreach ($ret_arr as $key => $value) {
                        common\test::webw_packet_add_money($value['server_id'], $value['player_name'], $value['ingot'], $value['gold'], $value['silver']);
                    }
                    $resData = [
                        'code' => 1,
                        'wait' => 2,
                        'msg' => '文件上传成功,扶持用户数据已经导入,请重新刷新查看！'
                    ];
                    return json($resData);
                } else {
                    // 上传失败获取错误信息
                    $resData = [
                        'code' => 0,
                        'wait' => 1,
                        'msg' => '文件上传失败，请重新导入！'
                    ];
                    return json($resData);
                }
            } else {
                $this->error('文件上传失败！');
            }
        } else {
            $this->assign(['meta_title' => '批量用户扶持文件上传']);
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
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['status'] = -1;
        $res = Db::connect('db_config_main')->table('recharge_data')->where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("recharge_del", "recharge_data", $ids, UID);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败！');
        }
    }

}
