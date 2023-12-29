<?php

namespace app\admin\controller;

use app\common\ChannelManage;
use think\Db;
use think\facade\Log;
use think\facade\View;

class GiftDataInfo extends Base
{
    public function index()
    {
        $search = trim(input('search'));
        $where[] = ['1', '=', 1];

        if ($search) {
            $where[] = ['gift_name', 'like', "%$search%"];
        }

        $common = trim(input('common'));
        if (isset($common)) {
            if ($common == 100) {
                $where[] = ['is_common', '=', 0];
            } elseif ($common == 1) {
                $where[] = ['is_common', '=', $common];
            }
        } else {
            $common = -1;
        }

        $field = "id,gift_name,gift_list,is_common,gift_amount,gift_code,valid_time,invalid_time,channel_no,card_length,csv_down_url";
        $lists = \app\admin\model\GiftDataInfo::field($field)
            ->where($where)
            ->order('id asc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'search' => $search,
            'common' => $common,
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '礼包数据列表'
        ]);
        return View::fetch();
    }

    /**
     * 新增礼包数据
     */
    public function create()
    {
        $channel_list = ChannelManage::getChannelList();
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            $giftValidate = new \app\admin\validate\GiftDataInfo();
            if (!$giftValidate->check($data)) {
                $this->error($giftValidate->getError());
            }

            $data['valid_time'] = strtotime($data['valid_time']);
            $data['invalid_time'] = strtotime($data['invalid_time']);

            $item_arr = explode(';', $data['gift_list']);

            $re = \app\admin\model\GiftDataInfo::insertGetId($data);
            if ($re) {
                //通用礼包配置的同时写入礼包码表（channel_gift_code）
                if ($data['is_common'] == 0) {
                    $gift['gift_code'] = $data['gift_code'];
                    $gift['gift_list'] = $data['gift_list'];
                    $gift['is_common'] = $data['is_common'];
                    $gift['valid_time'] = $data['valid_time'];
                    $gift['invalid_time'] = $data['invalid_time'];
                    $gift['gift_type'] = $re;

                    if (!\app\admin\model\ChannelGiftCode::insert($gift)) {
                        Log::write("配置通用礼包码同步添加配置ID:" . $re . "【channel_gift_code】表,插入失败!");
                    }
                }

                //添加行为记录
                action_log("gift_data_info_add", "gift_data_info", $re, UID);
                $down_url = $this->export($re);
                if (\app\admin\model\GiftDataInfo::update(['csv_down_url' => $down_url, 'id' => $re])) {
                    Log::write("礼包码csv文件地址更新成功!!!");
                } else {
                    Log::write("礼包码csv文件地址更新失败!!!");
                }
                $this->success('礼包配置数据新增成功!', 'gift_data_info/index');
            } else {
                $this->error('礼包配置数据新增失败!');
            }
        } else {
            $this->assign([
                'channel_list' => $channel_list,
                'meta_title' => '新增礼包配置数据'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 编辑礼包数据信息
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $info = \app\admin\model\GiftDataInfo::find($id);
        if (!$info) {
            $this->error('礼包配置数据不存在或已删除！');
        }
        if (\think\facade\Request::isPost()) {
            $data = $_POST;
            //验证
            $giftValidate = new \app\admin\validate\GiftDataInfo();
            if (!$giftValidate->check($data)) {
                $this->error($giftValidate->getError());
            }
            $data['valid_time'] = strtotime($data['valid_time']);
            $data['invalid_time'] = strtotime($data['invalid_time']);
            $re = \app\admin\model\GiftDataInfo::update($data);
            if ($re) {
                //添加行为记录
                action_log("gift_data_info_edit", "gift_data_info", $data['id'], UID);

                $gift_list_info['valid_time'] = $data['valid_time'];
                $gift_list_info['invalid_time'] = $data['invalid_time'];
                $where[] = [
                    ['gift_type', '=', $data['id']],
                    ['status', '=', 0]
                ];
                \app\admin\model\ChannelGiftCode::where($where)->update($gift_list_info);

                $this->success('礼包配置数据编辑成功', '');
            } else {
                $this->error('礼包配置数据编辑失败');
            }
        } else {

            $channel_list = ChannelManage::getChannelList();
            $this->assign([
                'id' => $id,
                'info' => $info,
                'channel_list' => $channel_list,
                'meta_title' => '编辑礼包配置数据信息'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 删除礼包数据信息
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $res = \app\admin\model\GiftDataInfo::where($where)->delete();
        if ($res) {
            //添加行为记录
            action_log("gift_data_info_del", "gift_data_info", $ids, UID);
            $this->success('礼包配置数据删除成功');
        } else {
            $this->error('礼包配置数据删除失败！');
        }
    }


    /**
     * 导出Excel数据
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function export($id)
    {
        $info = \app\admin\model\GiftDataInfo::find($id);
        if (!$info) {
            $this->error('礼包配置信息错误或已删除!');
        }

        $gift_type = \app\admin\model\GiftDataInfo::where('gift_name', '=', trim($info['gift_name']))
            ->order('id asc')
            ->limit(1)
            ->value('id');


        $xlsData = \app\admin\model\ChannelGiftCode::where([
            ['gift_type', '=', $gift_type]
        ])->field("gift_code,gift_list,is_common,from_unixtime(valid_time, '%Y-%m-%d %H:%i:%S') as valid_time,from_unixtime(invalid_time, '%Y-%m-%d %H:%i:%S') as invalid_time,gift_type,status,server_id")->select();


        //实例化
        $objExcel = new \PHPExcel();
        //设置文档属性
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        //设置内容
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter = explode(',', "A,B,C,D,E,F,G,H");
        $arrHeader = array('礼包码', '礼包物品列表', '是否通用', '生效时间', '失效时间', '礼包类型', '使用状态', '服务器ID');
        //填充表头信息
        $lenth = count($arrHeader);
        for ($i = 0; $i < $lenth; $i++) {
            $objActSheet->setCellValue("$letter[$i]1", "$arrHeader[$i]");
        }
        //填充表格信息
        foreach ($xlsData as $k => $v) {
            $k += 2;
            //表格内容
            $objActSheet->setCellValue('A' . $k, strval($v['gift_code']));
            $objActSheet->setCellValue('B' . $k, $v['gift_list']);
            $is_common = '';
            switch ($v['is_common']) {
                case 0:
                    $is_common = "通用";
                    break;
                case 1:
                    $is_common = "非通用";
                    break;
                default:
                    break;
            }

            $objActSheet->setCellValue('C' . $k, $is_common);
            $objActSheet->setCellValue('D' . $k, $v['valid_time']);
            $objActSheet->setCellValue('E' . $k, $v['invalid_time']);
            $objActSheet->setCellValue('F' . $k, $v['gift_type']);
            $status = '';
            switch ($v['status']) {
                case 0:
                    $status = '未使用';
                    break;
                case 1:
                    $status = '已使用';
                    break;
                case 2:
                    $status = '已失效';
                    break;
                default:
                    break;
            }
            $objActSheet->setCellValue('G' . $k, $status);
            $objActSheet->setCellValue('H' . $k, $v['server_id']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

        $width = array(20, 20, 15, 10, 10, 30, 10, 100);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[5]);
        $objActSheet->getColumnDimension('B')->setWidth($width[7]);
        $objActSheet->getColumnDimension('C')->setWidth($width[2]);
        $objActSheet->getColumnDimension('D')->setWidth($width[5]);
        $objActSheet->getColumnDimension('E')->setWidth($width[5]);
        $objActSheet->getColumnDimension('F')->setWidth($width[3]);
        $objActSheet->getColumnDimension('G')->setWidth($width[3]);
        $objActSheet->getColumnDimension('H')->setWidth($width[1]);


        $outfile = "gift_code_" . time() . ".xlsx";
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
