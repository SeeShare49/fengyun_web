<?php

namespace app\api\controller;

use app\admin\model\UserInfo;
use app\common\HttpSend;
use app\common\test;
use app\pay\model\RechargeData;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

require_once "common.php";

/**
 * 小七（娱马/漫方）
 */
class Seven
{
    const URL = "https://api.x7sy.com/user/check_v4_login";
    const AppKey = "749dc322bbac9cbdf29fc65761b30d44";
    const IosAppKey = "499e2f1debbfd9d504522beb53a58f74";
    const PUBLIC_KEY = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmnq9P9Z2Q2exWOSmX6pn+X08SWzXYLloa615KpNd9TQg7tXd5IBDC5GcIuwEcQxottxKpQ2+t7BnuAMMmLJEAg9m3YKoa3aFKYknilhuis+qZ5pYdRHhniJvArQ40rV9EZSH4O9/Hkz/FuxbACH+TUsVX6S/9y19UL/y8i8VeRwIDAQAB";

    /**
     * 获取guid
     * @param null $tokenKey
     * @param null $platform ios/android
     * @return \think\response\Json
     */
    public function getGuid($tokenKey = null, $platform = null)
    {
        if (isset($tokenKey) && isset($platform)) {
            if (trim($platform) == "ios") {
                $app_key = "499e2f1debbfd9d504522beb53a58f74"; //ios app key
            } else {
                $app_key = self::AppKey;//android app key
            }

            $sign = strtolower(md5($app_key . $tokenKey));
            $data['tokenkey'] = $tokenKey;
            $data['sign'] = $sign;

            $result = HttpSend::send_request(self::URL, $data);

            //成功返回结果
            //$result = '{"errorno":0,"tips_type":1,"tips_title":"","errormsg":"",
            //"data":{"guid":"33790001","is_real_user":"1","is_eighteen":"1","username":"x7demo",
            //"si":"efaa5074eb72691932505678ab667aca","pi":"1heliayojl74fg6k11reu77w344vy97vo0gyc9",
            //"di":"d3049ca1e25beef6067b943e70c50df4","source_os_type":"2"}}';

            //失败返回结果
            //{"errorno":-1,"tips_type":1,"tips_title":"",
            //"errormsg":"请【游戏方】按照【小7服务器端接入文档】中的对【白名单设置】的方式将当前IP地址【121.43.135.21】加入到当前【游戏方】的【开放平台】中的白名单！"}
            $result = json_decode($result, true);
            if (isset($result) && $result['errorno'] == 0) {
                $guid = $result['data']['guid'];
                return json(['code' => true, 'guid' => $guid, 'msg' => '获取guid成功']);
            }
            return json(['code' => false, 'msg' => $result['errormsg']]);
        }
        return json(['code' => false, 'msg' => 'token_key参数错误']);
    }

    /**
     * 获取订单信息（生成订单）
     */
    public function getOrderInfo()
    {
        Log::write("硬核支付接口来袭....");
        $param = Request::param();
        if (isset($param)) {
            $user_id = $param['user_id'];
            $sAccount = $param['sAccount'];
            $recharge_id = $param['recharge_id'];
            $type = $param['type'];
            $channel_id = $param['nChanneId'];
            $platform = $param['platform']; //ios/android
            $money = 0;//充值金额
            $amount = 0;//对应的充值道具数量
            if (isset($user_id) && isset($recharge_id) && isset($type)) {
                $info = $this->get_recharge_by_csv($recharge_id, $type);
                if (empty($info)) {
                    return json(['code' => false, 'msg' => '未匹配对应的充值信息']);
                }
                $money = isset($info['money']) ? $info['money'] : 0;
                $amount = $info['amount'];
            }
            $money = sprintf("%.2f", $money);
            $data['user_id'] = $user_id;
            $data['server_id'] = $param['server_id'];
            $data['recharge_id'] = $recharge_id;
            $data['amount'] = $amount;
            $data['money'] = $money;

            $order_id = 'XQ_' . $user_id . date('YmdHis');
            $data['order_id'] = $order_id; //渠道标识_+用户ID+日期+渠道号
            $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
            $data['remark'] = $sAccount;//remark （guid|tradeNo）
            $data['channel_id'] = $channel_id;
            $data['pay_ip'] = request()->ip();
            $data['real_server_id'] = $param['old_server_id'];

            $subject = $data['money'] . "_" . $amount . "元宝";
            if ($platform == "ios") {
                Log::write("platform:" . $platform);
                $public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDCerCz7EQLLKsnZvfG+TSkpOMTttq8MV82bLQOAf1uiQOVH4ztWbrl2oQ1UDrWtV/rXgZBTCbKNeRdohM1Y+k1TJTddWbxbV5xWzDGX+7JFy6J6cLzLN1ioHjoI1XUCFGVyxKNshqMwJr6Nc5CYtjZv/+n3RMTb073feg4kMgSEQIDAQAB";
                $data['pay_type'] = 2; //ios
            } else {
                Log::write("platform:" . $platform);
                $data['pay_type'] = 1;
                $public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmnq9P9Z2Q2exWOSmX6pn+X08SWzXYLloa615KpNd9TQg7tXd5IBDC5GcIuwEcQxottxKpQ2+t7BnuAMMmLJEAg9m3YKoa3aFKYknilhuis+qZ5pYdRHhniJvArQ40rV9EZSH4O9/Hkz/FuxbACH+TUsVX6S/9y19UL/y8i8VeRwIDAQAB";
            }
            Log::write("public key:" . $public_key);
            $sign_str = "game_area=" . $param['server_id'] . "&game_guid=" . $sAccount . "&game_orderid=" . $order_id . "&game_price=" . $money . "&subject=" . $subject . $public_key . "";
            $game_sign = md5($sign_str);
            Log::write("签名拼接参数:" . $sign_str);
            Log::write("MD5加密后的签名结果:" . $game_sign);
            Log::write("order_id:" . $data['order_id']);
            if (RechargeData::insertGetId($data)) {
                Log::write("订单编号【{$data['order_id']}】创建成功");
                return json(['code' => true, 'order_id' => $data['order_id'], 'amount' => $money, 'game_sign' => $game_sign, 'public_key' => $public_key, 'subject' => $subject, 'msg' => '订单创建成功']);
            } else {
                Log::write("订单编号【{$data['order_id']}】创建失败");
                return json(['code' => false, 'order_id' => $data['order_id'], 'amount' => $money, 'game_sign' => $game_sign, 'public_key' => $public_key, 'subject' => $subject, 'msg' => '订单创建失败']);
            }
        } else {
            return json(['code' => false, 'msg' => '参数错误']);
        }
    }

    /**
     * 小七支付回调
     */
    public function notify_url()
    {
        $input_string = file_get_contents("php://input");
        Log::write("小七支付回调input string:" . $input_string);

        Log::write(Request::param());
        $result = Request::param();

        if (isset($result) && !empty($result)) {
            $public_key = $result['extends_info_data'];
            //1、校验 sign_data
            $this->verify_sign_data($input_string, $public_key);
            //2、解密 encrypt_data 得到关键数据；这一步解密 encrypt_data 将会得到类似这样的字符串 game_orderid= 订单号&guid=游戏用户唯一标识&pay_price=商品金额
            //3、核对解密后的数据；游戏方需要判断这里三个信息 game_orderid、guid、pay_price 是否与【游戏方】 中游戏订单信息一致，三个信息都为一致视为订单成功，否则为失败！
            Log::write("url encode before encrypt data:" . $result['encryp_data']);
            Log::write("url encode after encrypt data:" . urlencode($result['encryp_data']));
            $this->get_encrypt_data($result['encryp_data'], $public_key);
            Log::write("public key:" . $public_key);
            $cpOrderNumber = $result['game_orderid'];
            $orderNumber = $result['xiao7_goid'];
            $info = $this->get_recharge_info($cpOrderNumber);
            if ($info) {
                if ($info['order_status'] == 1) {
                    return json(['code' => false, 'msg' => '【订单编号:' . $orderNumber . '】已发放道具,请勿重复提交!']);
                }
                Db::startTrans();
                try {
                    if ($info['order_status'] == 0) {
                        $remark = $info['remark'] . '|' . $orderNumber;
                        $new_data['user_id'] = $info['user_id'];
                        $new_data['server_id'] = $info['server_id'];
                        $new_data['recharge_id'] = $info['recharge_id'];
                        $new_data['pay_type'] = $info['pay_type'];
                        $new_data['channel_id'] = $info['channel_id'];
                        $new_data['add_time'] = $info['add_time'];
                        $new_data['amount'] = $info['amount'];
                        $new_data['remark'] = $remark; //'小七交易号【trade_no】:' . $orderNumber;
                        $new_data['money'] = $info['money'];
                        $new_data['pay_ip'] = $info['pay_ip'];
                        $new_data['order_id'] = $info['order_id'];
                        $new_data['real_server_id'] = $info['real_server_id'];

                        $ret = Db::connect('db_config_main')->table('recharge_data')->insertGetId($new_data);
                        Log::write("写入游戏充值表返回值:" . $ret);
                        if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);

                        //插入或更新用户充值统计数据
                        $this->get_recharge_statistics_info($info['user_id'], $info['money']);

                        //修改订单状态
                        $this->update_order_info($info['id'], $cpOrderNumber, $remark);

                        //充值元宝(命令发送服务器)
                        test::webw_packet_recharge($info['server_id'], $ret);
                    }
                    // 提交事务
                    Db::commit();
                    echo 'success';
                } catch (Exception $exception) {
                    Log::write('玩家充值事务回滚,exception:' . $exception);
                    Db::rollback();
                    echo 'failed';
                }
            } else {
                return 'failed:game_orderid error';
            }
        } else {
            Log::write("小七硬核支付回调参数错误!!!");
            return json(['code' => false, 'msg' => '支付回调参数错误!']);
        }
    }

    /**
     * 获取充值订单信息
     * @param $order_id  订单ID
     */
    public function get_recharge_info($order_id)
    {
        $info = RechargeData::where('order_id', '=', trim($order_id))->find();
        return $info ? $info : null;
    }

    /**
     * 更新用户充值统计信息
     * @param $user_id  账户ID
     * @param $money    充值金额
     */
    public function get_recharge_statistics_info($user_id, $money)
    {
        $user_info = UserRechargeStatistics::where('user_id', '=', $user_id)->find();
        if ($user_info) {
            $user_recharge['user_id'] = $user_info['user_id'];
            //判断数据表中的时间戳与当前时间戳的年份、月份是否一致
            if (date('y-m', $user_info['update_time']) == date('y-m', time())) {
                $user_recharge['month_recharge'] = $user_info['month_recharge'] + $money;
            } else {
                $user_recharge['month_recharge'] = $money;
            }
            $user_recharge['total_recharge'] = $user_info['total_recharge'] + $money;
            $user_recharge['update_time'] = time();
            $ret = UserRechargeStatistics::update($user_recharge);
            if (!$ret) {
                Log::write('用户ID【' . $user_id . '】更新充值统计信息失败!');
            }
        } else {
            $user_recharge['user_id'] = $user_id;
            $user_recharge['month_recharge'] = $money;
            $user_recharge['total_recharge'] = $money;
            $user_recharge['update_time'] = time();
            $ret = UserRechargeStatistics::insert($user_recharge);
            if (!$ret) {
                Log::write('用户ID【' . $user_id . '】添加充值统计信息失败!');
            }
        }
    }

    /**
     * 更新订单状态信息
     * @param $id           订单表ID
     * @param $order_id     订单编号
     * @param $remark       备注信息
     */
    public function update_order_info($id, $order_id, $remark)
    {
        $update['id'] = $id;
        $update['order_status'] = 1;
        $update['remark'] = $remark;
        if (!RechargeData::update($update)) {
            Log::write("订单号【out_trade_no】" . $order_id . "状态更新失败！！！");
        }
    }

    /**
     * 根据encrypt_data密文解密获取 game_order_id、guid、pay_price
     * @param $encrypt_data     RSA加密数据
     * @param $public_key       public_key
     */
    public function get_encrypt_data($encrypt_data, $public_key)
    {
        Log::write("get_encrypt_data encrypt data:" . $encrypt_data);
        Log::write("get_encrypt_data public key:" . $public_key);
        $public_key = ConvertPublicKey($public_key);
        $post_encryp_data_decode = base64_decode($encrypt_data);
        $decode_encryp_data = PublickeyDecodeing($post_encryp_data_decode, $public_key);
        parse_str($decode_encryp_data, $encryp_data_arr);
        if (!isset($encryp_data_arr["pay_price"]) || !isset($encryp_data_arr["guid"]) || !isset($encryp_data_arr["game_orderid"])) {
            Log::write("get_encrypt_data return encryp_data_decrypt_failed");
            ReturnResult('encryp_data_decrypt_failed');
        }
        $this->verify_order_info($encryp_data_arr["game_orderid"], $encryp_data_arr["guid"], $encryp_data_arr["pay_price"]);
    }

    public function test()
    {
        $encrypt_data = "tcLjw+0BTmwkBib6X/8tJ0Up+HC/E883CzfGK7h65Ao0DQ28Fay0n/waFkFylQMxy0VKfNxPxuC1u5qpZ08n4EwHc+GGwXcSKDuchz6DvtgWrpBJm3zd9v5G/FhbmNFhW9enUZOoq1nOZZ65DwfRJi4Ng+NPuqW4mRrpL4ldVZo=";
        $public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDCerCz7EQLLKsnZvfG+TSkpOMTttq8MV82bLQOAf1uiQOVH4ztWbrl2oQ1UDrWtV/rXgZBTCbKNeRdohM1Y+k1TJTddWbxbV5xWzDGX+7JFy6J6cLzLN1ioHjoI1XUCFGVyxKNshqMwJr6Nc5CYtjZv/+n3RMTb073feg4kMgSEQIDAQAB";

        $public_key = ConvertPublicKey($public_key);
        $post_encryp_data_decode = base64_decode($encrypt_data);
        $decode_encryp_data = PublickeyDecodeing($post_encryp_data_decode, $public_key);
        parse_str($decode_encryp_data, $encryp_data_arr);
        if (!isset($encryp_data_arr["pay_price"]) || !isset($encryp_data_arr["guid"]) || !isset($encryp_data_arr["game_orderid"])) {

            Log::write("get_encrypt_data return encryp_data_decrypt_failed");
            ReturnResult('encryp_data_decrypt_failed');
        }
        $order_id = $encryp_data_arr["game_orderid"];
        $price = $encryp_data_arr["pay_price"];
        $guid = $encryp_data_arr["guid"];
        return $this->verify_order_info($order_id, $guid, $price);
    }

    /**
     * 根据订单ID、用户唯一标识、支付金额校验订单数据是否一致
     * @param $order_id 订单ID
     * @param $guid     用户唯一标识
     * @param $price    支付金额
     */
    public function verify_order_info($order_id, $guid, $price)
    {
        Log::write("verify order info order_id param:" . $order_id);
        Log::write("verify order info guid param:" . $guid);
        Log::write("verify order info price param:" . $price);
        $where[] = [
            ['order_id', '=', trim($order_id)],
            ['money', '=', $price]
        ];
        Log::write("query order info sql:" . RechargeData::where($where)->whereRemark('like', '%' . $guid . '%')->fetchSql(true)->find());
        $order_info = RechargeData::where($where)->whereRemark('like', '%' . $guid . '%')->find();
        if (!isset($order_info)) {
            ReturnResult("failed:order error");
        }
    }

    /**
     * 校验sign_data是否匹配
     * @param $input_string
     * @param $public_key
     */
    public function verify_sign_data($input_string, $public_key)
    {
        Log::write("verify_sign_data input string:" . $input_string);
        Log::write("verify_sign_data public key:" . $public_key);
        parse_str($input_string, $post_data);
        $post_sign_data = base64_decode($post_data["sign_data"]);
        /************************************
         * 因为sign_data是不加入签名里面的
         ************************************/
        unset($post_data["sign_data"]);
        //按照参数名称的正序排序
        ksort($post_data);
        //对输入参数根据参数名排序，并拼接为key=value&key=value格式；
        $sourcestr = http_build_query_noencode($post_data);
        //对数据进行验签，注意对公钥做格式转换
//        $key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+zCNgOlIjhbsEhrGN7De2uYcfpwNmmbS6HYYI5KljuYNua4v7ZsQx5gTnJCZ+aaBqAIRxM+5glXeBHIwJTKLRvCxC6aD5Mz5cbbvIOrEghyozjNbM6G718DvyxD5+vQ5c0df6IbJHIZ+AezHPdiOJJjC+tfMF3HdX+Ng/VT80LwIDAQAB";
        $publicKey = ConvertPublicKey($public_key);
        $verify = Verify($sourcestr, $post_sign_data, $publicKey);

        Log::write("sourcestr:" . $sourcestr);
        Log::write("原public_key:" . $public_key);
        Log::write("Convert public key:" . $publicKey);

        //判断签名是否是正确
        if ($verify != 1) {
            Log::write("verify_sign_data return sign_data_verify_failed");
            ReturnResult('sign_data_verify_failed');
        }
    }

    /**
     * 获取角色信息(andriod包)
     * andriod APP_KEY
     */
    public function getUserRoleData()
    {
        Log::write("获取角色信息参数:");
        //https://api.x7sy.com/api_helper/get_user_role_data

//        $user_str = 34149896;// $_POST['user_str'];
//        $sign = '085d5517562d11714ca81129f21b56fb';// $_POST['sign'];
//        $game_area = 2;// $_POST['game_area'];

        $user_str = $_POST['user_str'];
        $sign = $_POST['sign'];
        $game_area = $_POST['game_area'];
        if (!isset($user_str) || empty($user_str)) {
            echo json_encode(array('errorno' => -3, "errormsg" => "用户唯一标识数据为空了！"));
            exit();
        }

        //sign计算对比
        $query_arr = array("user_str" => trim($user_str));
        $check_sign = md5($this->http_build_query_noencode($query_arr) . self::AppKey);//无转义
        if ($sign != $check_sign || empty($sign)) {
            echo json_encode(array('errorno' => -2, "errormsg" => "Sign值计算错误！"));
            exit();
        }

        if (empty($game_area)) {
            $user_data_list = $this->return_empty_struct($game_area);
            return $this->return_json_string('0', '区服错误', $user_str, $user_data_list);
        }

        if ($game_area == -1) {
            $user_data_list = $this->return_empty_struct($game_area);
            return $this->return_json_string('0', '不支持全服查询', $user_str, $user_data_list);
        }

        $user_split_array = explode('|', $user_str);
        $multi_user_data_list = array();
        foreach ($user_split_array as $value) {
            $user_id = UserInfo::where('UserName', '=', 'xiaoqiqd1' . $value)->value('UserID');
            if (isset($user_id)) {
                $field = 'actor_id,account_id,nickname,level,exp,job,gender,create_time,channel_id';
                $lists = dbConfigByReadBase($game_area)->table('player')->field($field)->where('account_id', '=', $user_id)->select();
                if (!isset($lists) || count($lists) == 0) {
                    $guid_data = $this->return_empty_struct($game_area);
                    $temp_user_str_list = $this->return_user_str_json($user_id, $guid_data);
                    array_push($multi_user_data_list, $temp_user_str_list);
                } else {
                    $user_data_list = array();
                    foreach ($lists as $key => $item) {
                        $user_data['area'] = $game_area;
                        $user_data['username'] = $item['nickname'];
                        $user_data['level'] = $item['level'];
                        $user_data['ce'] = '';
                        $user_data['stage'] = '';
                        $user_data['add_data'] = array('part1' => '', 'part2' => '');
                        array_push($user_data_list, $user_data);
                    }
                    $temp_user_str_list = $this->return_user_str_json($value, $user_data_list);
                    array_push($multi_user_data_list, $temp_user_str_list);
                }
            } else {
                $guid_data = $this->return_empty_struct($game_area);
                $temp_user_str_list = $this->return_user_str_json($value, $guid_data);
                array_push($multi_user_data_list, $temp_user_str_list);
            }
        }
        Log::write($this->return_multi_json_string(0, '', $multi_user_data_list));
        return $this->return_multi_json_string(0, '', $multi_user_data_list);
    }

    /**
     * IOS端获取角色信息
     * IOS APP_KEY
     */
    public function getIosUserRoleData()
    {
        $user_str = $_POST['user_str'];
        $sign = $_POST['sign'];
        $game_area = $_POST['game_area'];
        if (!isset($user_str) || empty($user_str)) {
            echo json_encode(array('errorno' => -3, "errormsg" => "用户唯一标识数据为空了！"));
            exit();
        }

        //sign计算对比
        $query_arr = array("user_str" => trim($user_str));
        $check_sign = md5($this->http_build_query_noencode($query_arr) . self::IosAppKey);//无转义
        if ($sign != $check_sign || empty($sign)) {
            echo json_encode(array('errorno' => -2, "errormsg" => "Sign值计算错误！"));
            exit();
        }

        if (empty($game_area)) {
            $user_data_list = $this->return_empty_struct($game_area);
            return $this->return_json_string('0', '区服错误', $user_str, $user_data_list);
        }

        if ($game_area == -1) {
            $user_data_list = $this->return_empty_struct($game_area);
            return $this->return_json_string('0', '不支持全服查询', $user_str, $user_data_list);
        }

        $user_split_array = explode('|', $user_str);
        $multi_user_data_list = array();
        foreach ($user_split_array as $value) {
            $user_id = UserInfo::where('UserName', '=', 'xiaoqiqd1' . $value)->value('UserID');
            if (isset($user_id)) {
                $field = 'actor_id,account_id,nickname,level,exp,job,gender,create_time,channel_id';
                $lists = dbConfigByReadBase($game_area)->table('player')->field($field)->where('account_id', '=', $user_id)->select();
                if (!isset($lists) || count($lists) == 0) {
                    $guid_data = $this->return_empty_struct($game_area);
                    $temp_user_str_list = $this->return_user_str_json($user_id, $guid_data);
                    array_push($multi_user_data_list, $temp_user_str_list);
                } else {
                    $user_data_list = array();
                    foreach ($lists as $key => $item) {
                        $user_data['area'] = $game_area;
                        $user_data['username'] = $item['nickname'];
                        $user_data['level'] = $item['level'];
                        $user_data['ce'] = '';
                        $user_data['stage'] = '';
                        $user_data['add_data'] = array('part1' => '', 'part2' => '');
                        array_push($user_data_list, $user_data);
                    }
                    $temp_user_str_list = $this->return_user_str_json($value, $user_data_list);
                    array_push($multi_user_data_list, $temp_user_str_list);
                }
            } else {
                $guid_data = $this->return_empty_struct($game_area);
                $temp_user_str_list = $this->return_user_str_json($value, $guid_data);
                array_push($multi_user_data_list, $temp_user_str_list);
            }
        }
        return $this->return_multi_json_string(0, '', $multi_user_data_list);
    }

    /**
     * 返回拼接后的json字符串
     * @param int $errorno
     * @param string $errormsg
     * @param $guid
     * @param $guid_data
     * @return false|string
     */
    public function return_json_string($errorno = 0, $errormsg = '', $guid, $guid_data)
    {
        $temp_user_data_array = array();
        $temp_user_data['guid'] = $guid;
        $temp_user_data['guid_data'] = $guid_data;
        array_push($temp_user_data_array, $temp_user_data);

        $final_user_data['errorno'] = $errorno;
        $final_user_data['errormsg'] = $errormsg;

        $final_user_data['user_str'] = $temp_user_data_array;
        Log::write($final_user_data);
        return json_encode($final_user_data);
    }

    /**
     * 返回空值的结构
     * @param $game_area
     * @return array
     */
    public function return_empty_struct($game_area)
    {
        $user_data_list = array();
        $user_data['area'] = $game_area;
        $user_data['username'] = '';
        $user_data['level'] = '';
        $user_data['ce'] = '';
        $user_data['stage'] = '';
        $user_data['add_data'] = array('part1' => '', 'part2' => '');
        array_push($user_data_list, $user_data);
        return $user_data_list;
    }

    /**
     * 拼接user_str的值
     * @param $guid
     * @param $guid_data
     */
    public function return_user_str_json($guid, $guid_data)
    {
        $user_str_data['guid'] = $guid;
        $user_str_data['guid_data'] = $guid_data;
        return $user_str_data;
    }

    /**
     * 返回一个或多个游戏账号角色信息
     * @param int $errorno
     * @param string $errormsg
     * @param $user_str_list
     * @return false|string
     */
    public function return_multi_json_string($errorno = 0, $errormsg = '', $user_str_list)
    {
        $final_user_data['errorno'] = $errorno;
        $final_user_data['errormsg'] = $errormsg;
        $final_user_data['user_str'] = $user_str_list;
        return json_encode($final_user_data);
    }


    /**
     * 拼接函数【无转义】
     * @param $query_arr
     * @param string $delimiter
     * @return string
     */
    function http_build_query_noencode($query_arr, $delimiter = "&")
    {
        if (empty($query_arr)) {
            return "";
        }
        $return_arr = array();
        array_walk($query_arr, function ($value, $item) use (&$return_arr) {
            $return_arr[$item] = "{$item}={$value}";
            if (empty($value) && !is_numeric($value)) unset($return_arr[$item]);
        });
        return !empty($return_arr) ? implode($delimiter, $return_arr) : "";
    }

    /**
     * 解密方法
     * $strEncode 密文
     * $keys 解密密钥 为游戏接入时分配的 callback_key
     * @param $strEncode
     * @param $keys
     * @return string
     */
    public function decode($strEncode, $keys)
    {
        if (empty($strEncode)) {
            return $strEncode;
        }
        preg_match_all('(\d+)', $strEncode, $list);
        $list = $list[0];
        if (count($list) > 0) {
            $keys = self::getBytes($keys);
            for ($i = 0; $i < count($list); $i++) {
                $keyVar = $keys[$i % count($keys)];
                $data[$i] = $list[$i] - (0xff & $keyVar);
            }
            return self::toStr($data);
        } else {
            return $strEncode;
        }
    }

    /**
     * 转成字符数据
     * @param $string
     * @return array
     */
    private static function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    /**
     * 转化字符串
     * @param $bytes
     * @return string
     */
    private static function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_data($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /**
     * 通过recharge_id与type获取充值信息
     * @param $recharge_id  充值ID
     * @param $type         充值类型
     * @return array
     */
    public function get_recharge_by_csv($recharge_id, $type)
    {
        $file_name = '../public/csv/recharge.csv';
        $file_open = fopen($file_name, 'r');
        // $data = fgetcsv($file_open, 100, ',');
        $count = 1;
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count >= 1) {
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[0] == $recharge_id && $data[1] == $type) {
                        $item['recharge_id'] = $data[0];
                        $item['type'] = $data[1];
                        $item['icon'] = $data[2];
                        $item['money'] = $data[3];
                        $item['amount'] = $data[4];
                    }
                    break;
                }
            }
            $count++;
        }
        fclose($file_open);
        return $item;
    }

    /**
     * 检测数据表是否存在
     * @param $table_name
     * @return
     * @throws \think\Exception
     */
    public function check_exists_table($table_name)
    {
        return Db::connect('db_config_main_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
    }
}
