<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\admin\model\UserInfo as UserInfoModel;

use app\common\ChannelManage;
use app\common\ServerManage;
use app\common\test;
use think\Db;
use think\facade\Log;
use think\facade\Request;

use app\admin\validate\UserInfo as UserInfoValidate;
use think\facade\View;

class UserInfo extends Base
{
    public $table_prefix = "cq_game";

    /**
     * 游戏用戶列表
     */
    public function index()
    {
        $where[] = ["1", '=', "1"];
        $username = trim(input('UserName'));
        $user_id = trim(input('UserID'));
        $phone = trim(input('Phone_UserName'));
        $gm = trim(input('gm'));
        $flag = trim(input('flag'));
        if ($username) {
            $where[] = ['UserName', 'like', "%$username%"];
        }

        if ($user_id) {
            $where[] = ['UserID', '=', $user_id];
        }

        if ($phone) {
            $where[] = ['Phone_UserName', '=', $phone];
        }

        if ($gm && $gm != -1) {
            $where[] = ['gm', '=', $gm];
        }

        if ($flag && $flag != -1) {
            $where[] = ['BanFlag', '=', $flag];
        }

        $lists = UserInfoModel::where($where)
            ->field('UserID,Phone_UserName,UserName,Play_Level,gm,RegisterTime,register_ip,StartBanTime,BanFlag,BanReason,ip_limit,changel_uid')
            ->order('UserID desc,RegisterTime desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'gm' => $gm,
            'flag' => $flag,
            'UserName' => $username,
            'user_id' => $user_id,
            'phone' => $phone,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '游戏用戶列表'
        ]);
        return $this->fetch();
    }


    /**
     *单个用户封号
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function seal($id)
    {
        $info = UserInfoModel::where('BanFlag', 0)->find($id);
        if (!$info) {
            $this->error("该游戏玩家信息不存在或已被封号!");
        }

        if (Request::isPost()) {
            $data = $_POST;
            clear_chat_log('db_chat_log',$this->table_prefix, $data['UserID']);
            test::webw_packet_ban_user($data['UserID'], 1, $data['BanReason']);
            return json(['code' => 1, 'msg' => '封停用户账号请求提交成功,待服务器处理......', 'data' => "norefresh"]);
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '游戏玩家封号'
            ]);
            return $this->fetch();
        }
    }


    /**
     *单个用户解封
     * @param $id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function unseal($id)
    {
        if (Request::isPost()) {
            $data['BanReason'] = "";
            test::webw_packet_ban_user($id, 0, $data['BanReason']);
            $this->success("用户账号解封请求提交成功,待服务器处理......", 'user_info/index');
        } else {
            $this->error("非法请求！！！");
        }
    }

    /**
     * 游戏玩家账户批量解封
     */
    public function batch_unseal()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = [
            ['UserID', 'in', $ids],
            ['BanFlag', '<>', 0],
        ];
        $data['BanFlag'] = 0;
        $res = UserInfoModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("user_batch_revoke", "userinfo", $ids, UID);
            $this->success('用户账号批量解封成功');
        } else {
            $this->error('用户账号批量解封失败！');
        }
    }

    /**
     * IP限制
     */
    public function ip_limit()
    {
        if (Request::isPost()) {
            $data['UserID'] = input('UserID');
            $data['ip_limit'] = input('ip_limit');

            $userInfoValidate = new UserInfoValidate();
            if (!$userInfoValidate->sceneIpLimit('ip_limit')->check($data)) {
                $this->error($userInfoValidate->getError());
            }
            $res = UserInfoModel::update($data);
            if ($res) {
                action_log("ip_limit", "userinfo", $data['UserID'], UID);
                $this->success('游戏玩家IP限制成功！');
            } else {
                $this->error('游戏玩家IP限制失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 账户关联相关信息
     * @param $user_id
     */
    public function relation_info($user_id)
    {
        $server_list = ServerManage::getServerList();
        $lists = array();
        $server_list_count = count($server_list);
        for ($i = 0; $i < $server_list_count; $i++) {

            $server_id = $server_list[$i]['id'];

            $serverInfo = ServerList::find($server_id);
            if ($serverInfo) {
                $table = $this->table_prefix . $serverInfo['real_server_id'];

                $lists_sql = "SELECT s.id as server_id,u.UserID ,p.actor_id,u.ChannelID,u.BanFlag,p.nickname,p.create_server_id,COALESCE(sum(r.money),0) as money from cq_main.server_list s,cq_main.user_info u 
inner JOIN {$table}.player p on u.UserID=p.account_id LEFT JOIN cq_main.recharge_data r on p.actor_id=r.user_id  where u.UserID={$user_id} and s.id={$server_id} GROUP BY p.actor_id";

                $Model = new UserInfoModel();
                $info = $Model->query($lists_sql);

                $info_count = count($info);
                if ($info_count > 0) {
                    for ($j = 0; $j < $info_count; $j++) {
                        array_push($lists, $info[$j]);
                    }
                }
            }
        }
        View::assign([
            'lists' => $lists,
            'meta_title' => '设备号关联角色信息'
        ]);
        return View::fetch();
    }


    /**
     * GM号设置
     */
    public function set_permit()
    {
        if (request()->isPost()) {
            $data['UserID'] = input('userid');
            $data['gm'] = input('val');
            $res = Db::connect('db_config_main')->table('user_info')->update($data);
            if ($res) {
                //添加行为记录
                action_log('gm_set', "userinfo", $data['UserID'], UID);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * 内部账号生成
     **/
    public function generate_account()
    {
        if (Request::isPost()) {
            $data = $_POST;
            if (intval($data['amount']) < 1 || intval($data['amount']) > 10) {
                $this->error('你要上天么?内部账号生成数量介于1到10!!!');
            }
            $codelenth = 8;
            $str = "0123456789";
            $len = strlen($str);
            for ($i = 0; $i < $data['amount']; $i++) {
                $key = '';
                for ($j = 0; $j < $codelenth; $j++) {
                    $temp = mt_rand(0, $len - 1);
                    $key .= $str[$temp];
                }
                $foo['cdkey'] = "911" . $key;
                //F833CC346DC3456CFD0FF17FFB444F9B:明文密码 yiwan666
                $account_list[] = ['Passsword' => 'F833CC346DC3456CFD0FF17FFB444F9B', 'RegisterTime' => date('Y-m-d H:i:s', time()), 'ChannelID' => 1, 'Phone_UserName' => $foo['cdkey'], 'register_ip' => Request::ip()];
            }
            $ret = UserInfoModel::insertAll($account_list);
            if ($ret) {
                action_log("user_generate", "user_info", 0, UID);
                $this->success('内部账号生成成功!', 'user_info/index');
            } else {
                $this->error('内部账号生成失败!');
            }
        } else {
            View::assign(['meta_title' => '内部账号生成']);
            return View::fetch();
        }
    }

    /**
     * 靓号生成
     **/
    public function best_account()
    {
        if (Request::isPost()) {
            $data = $_POST;
            if (UserInfoModel::where('Phone_UserName', '=', trim($data['phone']))->find()) {
                $this->error('靓号【' . $data['phone'] . '】已存在,请勿重复添加!!!');
            }
            $info['Phone_UserName'] = $data['phone'];
            //'F833CC346DC3456CFD0FF17FFB444F9B';
            $info['Passsword'] = strtoupper(md5($data['password']));
            $info['RegisterTime'] = date('Y-m-d H:i:s', time());
            $info['ChannelID'] = 1;
            $info['register_ip'] = Request::ip();

            $ret = UserInfoModel::insert($info);
            if ($ret) {
                action_log("user_generate", "user_info", 0, UID);
                $this->success('内部靓号生成成功!', 'user_info/index');
            } else {
                $this->error('靓号生成失败!');
            }
        } else {
            View::assign(['meta_title' => '内部靓号生成']);
            return View::fetch();
        }
    }


    /**
     * 绑定账号
     **/
    public function bind_account()
    {
        if (Request::isPost()) {
            $data = $_POST;
            if (!UserInfoModel::where('UserID', '=', $data['user_id'])->find()) {
                $this->error('账号ID【' . $data['user_id'] . '】不存在,请核对账号!!!');
            }

            if (UserInfoModel::where('Phone_UserName', '=', trim($data['phone']))->find()) {
                $this->error('靓号【' . $data['phone'] . '】已存在,请勿重复添加!!!');

            }
            $info['Phone_UserName'] = $data['phone'];
            $info['Passsword'] = strtoupper(md5($data['password']));

            if (UserInfoModel::where('UserID', '=', $data['user_id'])->update($info)) {
                $this->success('账号绑定成功!', 'user_info/index');
            } else {
                $this->error('账号绑定失败,请重试!');
            }
        } else {
            View::assign(['meta_title' => '账号绑定']);
            return View::fetch();
        }
    }

    /**
     * 重置密码
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reset_password($id)
    {
        $info = UserInfoModel::where('UserID', '=', $id)->find();
        if ($info) {
            $data['UserID'] = $id;
            //CE8C783383A96E5FCC204E6DAA1A2904 (明文密码：yw123abc)
            $data['Passsword'] = 'CE8C783383A96E5FCC204E6DAA1A2904';
            if (UserInfoModel::update($data)) {
                $this->success('密码重置成功!', 'user_info/index');
            } else {
                $this->error('密码重置失败,请重试!!!');
            }
        } else {
            $this->error('该账号不存在或已删除!!!');
        }
    }

    /**
     * 用户密码修改
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_password()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $user_id = $data['user_id'];
            $info = UserInfoModel::where('UserID', '=', $user_id)->find();
            if (!$info) {
                $this->error('该账号不存在或已删除！');
            }
            //'F833CC346DC3456CFD0FF17FFB444F9B';
            $info['Passsword'] = strtoupper(md5($data['password']));

            $ret = UserInfoModel::where('UserID', '=', $user_id)->update(['Passsword' => $info['Passsword']]);
            if ($ret) {
                action_log("user_password_edit", "user_info", 0, UID);
                $this->success('玩家用户密码修改成功!', 'user_info/index');
            } else {
                $this->error('玩家用户密码修改失败!');
            }
        } else {
            View::assign([
                'meta_title' => '玩家用户密码修改'
            ]);
            return View::fetch();
        }
    }

    /**
     * 角色转移
     *
     * @throws \think\Exception
     */
    public function role_transfer()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $origin_user_id = $data['origin_user_id']; //用户ID
            $transfer_user_id = $data['transfer_actor_id']; //角色ID
            $transfer_server_id = $data['server_id'];

            $origin_user_info = UserInfoModel::where('UserID', '=', $origin_user_id)->find();
            if (!$origin_user_info) {
                $this->error('原ID【' . $origin_user_id . '】账号不存在或已删除！');
            } else {
                $server_id = get_real_server_id($transfer_server_id);
                $transfer_user_info = dbConfigByReadBase($server_id)->table('player')->field('account_id,actor_id')->where('actor_id', '=', $transfer_user_id)->find();

                if (!$transfer_user_info) {
                    $this->error('角色ID【' . $transfer_user_id . '】不存在或已删除！');
                } else {
                    $temp_user_id = $transfer_user_info['account_id'];
                    if ($transfer_user_info['account_id'] == $origin_user_id) {
                        $this->error('角色ID【' . $transfer_user_id . '】已归属用户ID【' . $origin_user_id . '】,请勿重复转移!');
                    } else {
                        /** 转移角色前先将原游戏用户踢出游戏 **/
                        test::webw_packet_kick_off_player(strval($transfer_user_info['account_id']), $transfer_server_id);

                        $transfer_info['account_id'] = $origin_user_id;
                        $ret = dbConfig($server_id)->table('player')->where('actor_id', '=', $transfer_user_id)->update($transfer_info);
                        if ($ret) {
                            $this->update_server_actor_number($origin_user_id, $transfer_server_id, $temp_user_id);
                            $remark = '原服务器ID【' . $transfer_server_id . '】,角色ID【' . $transfer_user_id . '】转移到账号ID【' . $origin_user_id . '】.';
                            action_log("user_role_transfer", "user_info", 0, UID, $remark);
                            $this->success('角色转移成功!', 'user_info/index');
                        } else {
                            $this->error('角色转移失败!');
                        }
                    }
                }
            }
        } else {
            $server_list = ServerManage::getServerList();
            View::assign([
                'server_id' => trim(input('server_id')),
                'server_list' => $server_list,
                'meta_title' => '角色转移'
            ]);
            return View::fetch();
        }
    }

    /**
     * 编辑服务器对应角色数（角色转移）
     * @param $user_id      新角色转移至新用户下的用户ID
     * @param $server_id    服务器ID
     * @param $temp_user_id    被转移的用户ID
     */
    public function update_server_actor_number($user_id, $server_id, $temp_user_id)
    {
        $userInfo = Db::connect('db_config_main_read')->table('user_info')->where('UserID', '=', trim($user_id))->find();
        if ($userInfo) {
            if (Db::connect('db_config_main')->table('server_actor_number')->where('user_id', '=', trim($user_id))->setInc('actor_number')) {
                Log::write("用户ID【" . $user_id . "】角色转移对应服务器角色数量新增成功！");
            }
        } else {
            $new_user_info['user_id'] = $user_id;
            $new_user_info['server_id'] = $server_id;
            $new_user_info['actor_number'] = 1;
            if (Db::connect('db_config_main')->table('server_actor_number')->insert($new_user_info)) {
                Log::write("用户ID【" . $user_id . "】角色转移对应服务器数据不存在,新增角色数量信息成功！");
            }
        }

        if (Db::connect('db_config_main')->table('server_actor_number')->where('user_id', '=', trim($temp_user_id))->setDec('actor_number')) {
            Log::write("用户ID【" . $temp_user_id . "】角色转移对应服务器角色数量删减成功！");
        }
    }

    /**
     * 渠道迁移
     **/
    public function channel_transfer()
    {
        $channel_list = ChannelManage::getChannelList();
        if (Request::isPost()) {
            $data = $_POST;
            $ret = UserInfoModel::where('ChannelID', '=', trim($data['channel_id']))->update(['ChannelID' => trim($data['change_channel_id'])]);
            if ($ret) {
                $this->success('渠道转移成功!', 'user_info/index');
            } else {
                $this->error('渠道转移失败!');
            }
        } else {
            View::assign([
                'channel_list' => $channel_list,
                'meta_title' => '渠道迁移'
            ]);
            return View::fetch();
        }
    }


    /**
     * 玩家IP归属导出（尽可能不用，太消耗数据库内存）
     **/
    public function export()
    {
        try {
            $user_lists = UserInfoModel::field('UserID,register_ip')->select();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        $xlsData = array();
        $user_lists_count = count($user_lists);
        for ($k = 0; $k < $user_lists_count; $k++) {

            if (isset($user_lists[$k]['register_ip']) && !empty($user_lists[$k]['register_ip'])) {
                $sql_str = "SELECT province,city FROM yw_ips where INET_ATON('" . $user_lists[$k]['register_ip'] . "') BETWEEN ip_start_num AND ip_end_num LIMIT 1";
                $Model = new \app\admin\model\IPS();
                $info = $Model->query($sql_str);
                if ($info) {
                    $data['UserID'] = $user_lists[$k]['UserID'];
                    $data['register_ip'] = $user_lists[$k]['register_ip'];
                    $data['province'] = $info[0]['province'];
                    $data['city'] = $info[0]['city'];
                    array_push($xlsData, $data);
                }
            }
        }

        //实例化
        $objExcel = new \PHPExcel();
        //设置文档属性
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        //设置内容
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter = explode(',', "A,B,C,D");
        $arrHeader = array('账号ID', '注册IP', '省份', '城市');
        //填充表头信息
        $lenth = count($arrHeader);
        for ($i = 0; $i < $lenth; $i++) {
            $objActSheet->setCellValue("$letter[$i]1", "$arrHeader[$i]");
        };
        //填充表格信息
        foreach ($xlsData as $k => $v) {
            $k += 2;
            //表格内容
            $objActSheet->setCellValue('A' . $k, $v['UserID']);
            $objActSheet->setCellValue('B' . $k, $v['register_ip']);
            $objActSheet->setCellValue('C' . $k, $v['province']);
            $objActSheet->setCellValue('D' . $k, $v['city']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

        $width = array(20, 20, 15, 10, 10, 30, 10, 50);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[7]);
        $objActSheet->getColumnDimension('B')->setWidth($width[4]);
        $objActSheet->getColumnDimension('C')->setWidth($width[7]);
        $objActSheet->getColumnDimension('D')->setWidth($width[3]);

        //$outfile = md5("充值记录" . time()) . ".xlsx";
        $outfile = "用户注册IP查询_" . time() . ".xlsx";
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

    /**
     * 获取渠道列表
     * @param $id 被选中的渠道ID
     * @return mixed
     */
    public function getChannelListBySelectId($id)
    {
        return ChannelManage::getChannelListExcludeId($id);
    }
}
