<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\admin\model\UserChannel;
use app\common\ChannelManage;
use app\common\ServerManage;
use app\common\test;
use think\facade\Log;

define('GROUP_ID', config('admin.GROUP_ID'));
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));//混服管理组

class RechargeData extends Base
{
    public function index()
    {
        /**
         * 混服组特殊处理
         **/
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $channel_list = ChannelManage::getChannelListByIds($channel_ids);
            $where[] = ['channel_id', 'in', $channel_ids];
        } else {
            $channel_list = ChannelManage::getChannelList();
            $where[] = ['1', '=', 1];
        }

        $is_guild = false;
        $ids = '';
        $temp_server_ids = '';
        if (GROUPID == GROUP_ID) {
            $is_guild = true;
            $s_ids = get_user_server_list(UID);
            $temp_server_ids = '';
            foreach ($s_ids as $key => $value) {
                $temp_server_ids .= $value['server_id'] . ',';
            }
            $ids = explode(',', rtrim($temp_server_ids, ","));
            $server_list = ServerManage::getServerListByIds($ids);
        } else {
            $server_list = ServerManage::getServerList();
        }
        $this->assign('serverlist', $server_list);


        $user_id = trim(input('user_id'));

        if ($user_id) {
            $where[] = ['user_id', '=', $user_id];
        }

        $server_id = trim(input('server_id'));
        if ($server_id) {
            if ($is_guild) {
                $where[] = ['server_id', '=', rtrim($temp_server_ids, ",")];
            } else {
                $where[] = ['server_id', '=', $server_id];
            }
        }

        if (empty($server_ids) && $is_guild == true) {
            $where[] = ['server_id', '=', rtrim($temp_server_ids, ",")];
        }

        $channel_id = trim(input('channel_id'));
        if ($channel_id) {
            $where[] = ['channel_id', '=', $channel_id];
        }

        $order_status = trim(input('order_status'));
        if ($order_status == 100) {
            $where[] = ['order_status', '=', 0];
        } else if ($order_status == 2) {
            $where[] = ['order_status', '=', 2];
        } else {
            $order_status = 1;
            $where[] = ['order_status', '=', 1];
        }

        //是否对账（财务对账状态）
        $is_check = trim(input('is_check'));
        if ($is_check == 100) {
            $where[] = ['is_check', '=', 0];
        } elseif ($is_check == 1) {
            $where[] = ['is_check', '=', $is_check];
        }

        //支付方式
        $pay_type = trim(input('pay_type'));
        if ($pay_type && $pay_type != 0) {
            $where[] = ['pay_type', '=', $pay_type];
        }

        $amount = trim(input('amount'));
        if ($amount && $amount != -1) {
            $where[] = ['money', '=', $amount];
        }

        //订单编号
        $order_id = trim(input('order_id'));
        if ($order_id) {
            $where[] = ['order_id', '=', $order_id];
        }

        //起始时间查询
        $start_date = trim(input('start_date'));
        if ($start_date) {
            //$start = $start_date . " " . "00:00:00";
            $where[] = ['add_time', '>=', $start_date];
        }
        //结束时间查询
        $end_date = trim(input('end_date'));
        if ($end_date) {
            //$end = $end_date . " " . "23:59:59";
            $where[] = ['add_time', '<=', $end_date];
        }

        //统计充值总额
        $total_money = 0;
        $rechargeInfo = \app\pay\model\RechargeData::field('sum(money) as total')->where($where)->where('order_status=1')->find();
        if ($rechargeInfo) {
            $total_money = $rechargeInfo['total'];
        }

        $lists = \app\pay\model\RechargeData::field('id,server_id,user_id,money,amount,order_id,pay_ip,order_status,channel_id,is_check,pay_type,add_time')->where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'amount' => $amount,
            'total_money' => $total_money,
            'user_id' => $user_id,
            'server_id' => $server_id,
            'lists' => $lists,
            'is_check' => $is_check,
            'order_status' => $order_status,
            'order_id' => $order_id,
            'channel_list' => $channel_list,
            'channel_id' => $channel_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pay_type' => $pay_type,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'page' => $page,
            'meta_title' => '系统充值列表'
        ]);
        return $this->fetch();
    }

    /**
     * 财务专用
     * 设置对账状态
    **/
    public function set_order_check()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $where[] = [['id', 'in', $ids]];
        $data['is_check'] = 1;

        $ret =\app\pay\model\RechargeData::where($where)->update($data);
        if ($ret) {
            action_log('order_check_status', 'recharge_data', $ret, UID);
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 1,
                'msg' => '订单核对状态修改完成!'
            ];
            return json($resData);
        } else {
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 0,
                'msg' => '订单核对状态修改失败!'
            ];
            return json($resData);
        }
    }

    /**
     * 充值记录导出
     **/
    public function export()
    {
        try {
            $where[] = ['order_status', '=', 1];

            $channel_id = trim(input('channel_id'));
            if ($channel_id) {
                $where[] = ['channel_id', '=', $channel_id];
            }

            $server_id = trim(input('server_id'));
            if ($server_id) {
                $where[] = ['server_id', '=', $server_id];
            }
            $start_date = trim(input('start_date'));
            if ($start_date) {
                $where[] = ['add_time', '>=', $start_date];
            }
            //结束时间查询
            $end_date = trim(input('end_date'));
            if ($end_date) {
                $where[] = ['add_time', '<=', $end_date];
            }

            //是否对账（财务对账状态）
            $is_check = trim(input('is_check'));
            if ($is_check == 100) {
                $where[] = ['is_check', '=', 0];
            } elseif ($is_check == 1) {
                $where[] = ['is_check', '=', $is_check];
            }

            $xlsData = \app\pay\model\RechargeData::field('id,server_id,order_id,money,pay_type,add_time,channel_id,remark,is_check')->where($where)->select();

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

//
//        Vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
//        Vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
//        Vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        //实例化
        $objExcel = new \PHPExcel();
        //设置文档属性
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        //设置内容
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter = explode(',', "A,B,C,D,E,F,G,H,I");
        $arrHeader = array('编号', '服务器ID', '订单号', '充值金额', '支付方式', '充值时间', '渠道来源', '备注信息','对账状态');
        //填充表头信息
        $lenth = count($arrHeader);
        for ($i = 0; $i < $lenth; $i++) {
            $objActSheet->setCellValue("$letter[$i]1", "$arrHeader[$i]");
        };
        //填充表格信息
        foreach ($xlsData as $k => $v) {
            $k += 2;
            //表格内容
            $objActSheet->setCellValue('A' . $k, strval($v['id']));
            $objActSheet->setCellValue('B' . $k, get_server_name($v['server_id']));
            $objActSheet->setCellValue('C' . $k, $v['order_id']);
            $objActSheet->setCellValue('D' . $k, $v['money']);
            $pay_type = '';
            switch ($v['pay_type']) {
                case 1:
                    $pay_type = "支付宝";
                    break;
                case 2:
                    $pay_type = "微信支付";
                    break;
                case 3:
                    $pay_type = "汇付宝";
                    break;
                default:
                    break;
            }

            $objActSheet->setCellValue('E' . $k, $pay_type);
            $objActSheet->setCellValue('F' . $k, $v['add_time']);
            $objActSheet->setCellValue('G' . $k, get_channel_name($v['channel_id']));
            $objActSheet->setCellValue('H' . $k, $v['remark']);
            $check_status ='';//是否对账
            switch ($v['is_check']) {
                case 0:
                    $check_status = "未对账";
                    break;
                case 1:
                    $check_status = "已对账";
                    break;
                default:
                    break;
            }
            $objActSheet->setCellValue('I' . $k,$check_status);
            // 图片生成
            //$objDrawing[$k] = new \PHPExcel_Worksheet_Drawing();
            //$objDrawing[$k]->setPath(ROOT_PATH."public/static/image/playbtn.png");
            // 设置宽度高度
            //$objDrawing[$k]->setHeight(40);//照片高度
            //$objDrawing[$k]->setWidth(40); //照片宽度
            // 设置图片要插入的单元格
            //$objDrawing[$k]->setCoordinates('C' . $k);
            // 图片偏移距离
            //$objDrawing[$k]->setOffsetX(30);
            //$objDrawing[$k]->setOffsetY(12);
            //$objDrawing[$k]->setWorksheet($objExcel->getActiveSheet());

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

        $width = array(20, 20, 15, 10, 10, 30, 10, 50);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[7]);
        $objActSheet->getColumnDimension('B')->setWidth($width[4]);
        $objActSheet->getColumnDimension('C')->setWidth($width[7]);
        $objActSheet->getColumnDimension('D')->setWidth($width[3]);
        $objActSheet->getColumnDimension('E')->setWidth($width[5]);
        $objActSheet->getColumnDimension('F')->setWidth($width[1]);
        $objActSheet->getColumnDimension('G')->setWidth($width[2]);
        $objActSheet->getColumnDimension('H')->setWidth($width[5]);
        $objActSheet->getColumnDimension('I')->setWidth($width[2]);

        //$outfile = md5("充值记录" . time()) . ".xlsx";
        $outfile = time() . ".xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
}
