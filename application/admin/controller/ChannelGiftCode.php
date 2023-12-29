<?php

namespace app\admin\controller;

use app\admin\common\Export;
use app\admin\model\ActivationCode as CodeModel;
use app\common\GiftDataInfoManage;
use app\common\ServerManage;
use think\Db;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

use PHPExcel;

require_once dirname(dirname(__FILE__)) . '/common/Export.php';

/**
 * 渠道礼物激活码
 */
class ChannelGiftCode extends Base
{
    public function index()
    {
        $gift_type_list = GiftDataInfoManage::getGiftTypeList();
        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        $use_actor_name = trim(input('use_actor_name'));
        $use_actor_id = trim(input('use_actor_id'));
        $gift_type = trim(input('gift_type'));
        $gift_code = trim(input('gift_code'));
        $status = trim(input('status'));

        $id = Request::param('gift_type');
        $where[] = ['1', '=', 1];

        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        if ($use_actor_id) {
            $where[] = ['use_actor_id', '=', $use_actor_id];
        }
        if ($use_actor_name) {
            $where[] = ['use_actor_name', 'like', "%$use_actor_name%"];
        }

        if($id)
        {
            $where[] = ['gift_type', '=', $id];
        }

        if ($gift_type) {
            $where[] = ['gift_type', '=', $gift_type];
        }

        if ($gift_code) {
            $where[] = ['gift_code', '=', $gift_code];
        }

        if ($status && $status != -1) {
            //特殊判断$gift_type==0
            if ($status == 100) {
                $where[] = ['status', '=', 0];
            } else {
                $where[] = ['status', '=', $status];
            }
        }

        $lists = \app\admin\model\ChannelGiftCode::where($where)
            ->order('status desc,gift_type asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'gift_type_list' => $gift_type_list,
            'gift_type' => $gift_type,
            'gift_code' => $gift_code,
            'status' => $status,
            'server_id' => $server_id,
            'server_list' => $server_list,
            'use_actor_id' => $use_actor_id,
            'use_actor_name' => $use_actor_name,
            'lists' => $lists,
            'page' => $page,
            'empty'=>'<td class="empty" colspan="12">暂无数据</td>',
            'meta_title' => '礼包码列表数据列表'
        ]);
        return View::fetch();
    }

    /**
     * 生成礼包码
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function generate($id)
    {
        $info = \app\admin\model\GiftDataInfo::find($id);
        if ($info) {
            //2	官网下载龙腾天下礼包福利	22000129|1;22000158|2;11104100|10	1	1000	11		1616688000	1622390400	1	0

            $gift_type = $id;
            $checkInfo = \app\admin\model\GiftDataInfo::where('gift_name', '=', trim($info['gift_name']))->order('id asc')->limit(1)->find();
            if ($checkInfo) {
                $gift_type = $checkInfo['id'];
            }

            if ($info['is_common'] == 0) {
                $this->error('非通用礼包不允许生成礼包码!');
            }

            $amount = $info['gift_amount'] ? intval($info['gift_amount']) : 0;
            if ($amount < 1) {
                $this->error('礼包码数量必须大于0!');
            }

            $codelenth = $info['card_length'] ? intval($info['card_length']) : 9;
            $str = 'ABCDEFGHLJKMNOPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            $len = strlen($str);
            $d = date("m");

            for ($i = 0; $i < $amount; $i++) {
                $key = '';
                for ($j = 0; $j < $codelenth; $j++) {
                    $temp = mt_rand(0, $len - 1);
                    $key .= $str[$temp];//激活码
                }
                $foo['cdkey'] = $d . $key;
                $code[] = ['gift_code' => $foo['cdkey'], 'gift_list' => $info['gift_list'], 'is_common' => $info['is_common'], 'valid_time' => $info['valid_time'],
                    'invalid_time' => $info['invalid_time'], 'gift_type' => $gift_type];
            }
            $res = \app\admin\model\ChannelGiftCode::insertAll($code);
            if ($res) {
                action_log("channel_gift_code_add", "channel_gift_code", 0, UID);
                $this->success('礼包码生成成功!', 'channel_gift_code/index');
            } else {
                $this->error('礼包码生成失败,请重试!');
            }
        }
    }

    /**
     * 礼包码导出
     * @param $res
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export_excel($res)
    {
        // 实例化excel类
        $objPHPExcel = new PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('礼包码列表');

        // 设置表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);


        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //  ->field('gift_code,gift_list,is_common,valid_time,invalid_time,gift_type')
        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '礼包码');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '礼包列表');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '是否通用');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '生效时间');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '失效时间');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '礼包类型');

        // 数据起始行
        $row_num = 2;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'J' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            // 设置价格为数字格式
            $objPHPExcel->getActiveSheet()->getStyle('D' . $row_num)->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
            // 居中
            $objPHPExcel->getActiveSheet()->getStyle('E' . $row_num . ':' . 'H' . $row_num)->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // 设置单元格数值
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $value['gift_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['gift_list']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['is_common']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['valid_time']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['invalid_time']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['gift_type']);
            $row_num++;
        }


        Log::write("rows count:" . $row_num);

        $outputFileName = 'gift_code_' . time() . '.xls';
        $xlsWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        var_dump($xlsWriter);
        var_dump($objPHPExcel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $xlsWriter->save("php://output");
//        echo file_get_contents($outputFileName);
    }

}
