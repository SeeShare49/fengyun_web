<?php

namespace app\admin\controller;

use app\common\ChannelManage;
use app\common\ServerManage;
use think\facade\View;

class PurchaseData extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        /** @var TYPE_NAME $where */
        $where[] = ['1', '=', 1];
        $channel_list = ChannelManage::getChannelList();
        $server_id = trim(input('server_id'));
        if (isset($user_id)) {
            $where[] = ['server_id', '=', $server_id];
        }
        //直充表ID
        $recharge_id = trim(input('recharge_id'));
        if ($recharge_id) $where[] = ['recharge_id', '=', $recharge_id];
        //渠道ID
//        $channel_id = trim(input('channel_id'));
//        if ($channel_id) $where[] = ['channel_id', '=', $channel_id];
        $user_id = trim(input('user_id'));
        if ($user_id) $where[] = ['user_id', '=', $user_id];
        //支付方式
        $pay_type = trim(input('pay_type'));
        if ($pay_type && $pay_type != 0) {
            $where[] = ['pay_type', '=', $pay_type];
        }
        //订单编号
        $order_id = trim(input('order_id'));
        if ($order_id) {
            $where[] = ['order_id', '=', $order_id];
        }
        //订单状态（支付状态）
        $order_status = trim(input('order_status'));
        if ($order_status == 100) {
            $where[] = ['order_status', '=', 0];
        } else if ($order_status == 2) {
            $where[] = ['order_status', '=', $order_status];
        } else if ($order_status == 1) {
            $where[] = ['order_status', '=', $order_status];
        } else {
            $order_status = -1;
        }

        //起始时间查询
        $start_date = trim(input('start_date'));
        if ($start_date) {
            $where[] = ['add_time', '>=', $start_date];
        }
        //结束时间查询
        $end_date = trim(input('end_date'));
        if ($end_date) {
            $where[] = ['add_time', '<=', $end_date];
        }

        //统计充值总额
        $total_money = \app\admin\model\PurchaseData::where('order_status=1')->sum('money');
        $lists = \app\admin\model\PurchaseData::field('id,server_id,user_id,purchase_name,money,amount,order_id,pay_ip,order_status,channel_id,pay_type,add_time')->where($where)
            ->order('order_status desc,id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'server_list' => $server_list,
            'channel_list' => $channel_list,
            'user_id' => $user_id,
            'lists' => $lists,
            'total_money' => $total_money,
            'order_id' => $order_id,
//            'channel_id' => $channel_id,
            'order_status' => $order_status,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pay_type' => $pay_type,
            'recharge_id' => $recharge_id,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'page' => $page,
            'meta_title' => '直购道具列表'
        ]);
        return View::fetch();
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

            $xlsData = \app\pay\model\RechargeData::field('id,server_id,order_id,money,pay_type,add_time,channel_id,remark,is_check')->where($where)->select();

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        //实例化
        $objExcel = new \PHPExcel();
        //设置文档属性
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        //设置内容
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter = explode(',', "A,B,C,D,E,F,G,H");
        $arrHeader = array('编号', '服务器ID', '订单号', '充值金额', '支付方式', '充值时间', '渠道来源', '备注信息');
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
