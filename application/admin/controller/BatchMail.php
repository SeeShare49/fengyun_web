<?php

namespace app\admin\controller;

use app\admin\model\ActivityType as TypeModel;
use app\admin\model\PropCsv;
use app\common\CsvManage;
use app\common\ServerManage;
use think\facade\Log;
use think\facade\View;

use app\common;

/**
 * 批量邮件发送
 */
class BatchMail extends Base
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        $title = trim(input('mail_title'));
        $player_id = trim(input('player_id'));
        $where[] = ['status', '=', 1];
        if ($title) {
            $where[] = ['mail_title', 'like', "%$title%"];
        }

        if ($player_id) {
            $where[] = ['player_id', '=', $player_id];
        }

        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (!empty($server_id) && $server_id != -1) {
            $server_ids = explode(',', $server_id);
            $where[] = ['server_id', 'in', $server_id];
        }

        $lists = db('batch_mail')
            ->where($where)
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        View::assign([
            'mail_title' => $title,
            'player_id' => $player_id,
            'server_id' => $server_ids,
            'server_list' => $server_list,
            'page' => $page,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '批量邮件列表'
        ]);
        return View::fetch();
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

                $result = CsvManage::load_input_csv($handle); // 解析csv
                $len_result = count($result);

                if ($len_result == 0) {
                    $this->error('此文件中没有数据！');
                }
                $ret_arr = array();
                for ($i = 1; $i < $len_result + 1; $i++) {
                    // 循环获取各字段值
                    $arr = @array_values($result[$i]);
                    $data['server_id'] = $arr[0];
                    $data['player_id'] = $arr[1];
                    $data['mail_title'] = $arr[2];// mb_convert_encoding($arr[2], "UTF-8", "UTF-8");
                    $data['mail_content'] = $arr[3];// mb_convert_encoding($arr[3], "UTF-8", "UTF-8");
                    $data['prop_list'] = $arr[4];
                    $data['mail_date'] = time();
                    array_push($ret_arr, $data);
                }
                fclose($handle); // 关闭指针
                /** 批量插入数据表中 **/
                $result = \app\admin\model\BatchMail::insertAll($ret_arr);
                if ($result) {
                    $resData = [
                        'code' => 1,
                        'msg' => '文件上传成功，数据已经导入！'
                    ];
                    return json($resData);
                } else {
                    $resData = [
                        'code' => 0,
                        'msg' => '文件上传失败，请重新导入！'
                    ];
                    return json($resData);
                }
            } else {
                $this->error('文件上传失败！');
            }
        } else {
            $this->assign(['meta_title' => '批量邮件文件上传']);
            return $this->fetch();
        }
    }

    /**
     * 发送
     * @return \think\Response
     */
    public function send()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        /**
         *  common\test::mail($data['server_id'], $data['nick_name'], $data['title'], $data['content'], $prop_info);
         * $this->result($data, 1, '个人邮件发送成功,待服务器处理......');
         **/

        $count = count($ids);
        for ($i = 0; $i < $count; $i++) {
            $info = \app\admin\model\BatchMail::find($ids[$i]);
            if ($info) {
                $player_name = get_player_name($info['player_id'], $info['server_id']);
                common\test::mail($info['server_id'], $player_name, $info['mail_title'], $info['mail_content'], $info['prop_list']);
            }
        }
        action_log('batch_send_mail','batch_mail',null,UID);
        $update_data['status'] = 1;
        $where[] = ['id', 'in', $ids];
        \app\admin\model\BatchMail::where($where)->update($update_data);
        $this->success('邮件发送成功!');
    }


    /**
     * 清空道具
     */
    public function clear()
    {
        $res = \app\admin\model\BatchMail::where('1=1')->delete();
        if ($res !== false) {
            action_log('batch_clear_mail','batch_mail',null,UID);
            $this->success('批量邮件清空成功！', '');
        } else {
            $this->error('批量邮件清空失败！');
        }
    }
}
