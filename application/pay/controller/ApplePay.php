<?php


namespace app\pay\controller;


use app\common\test;
use app\pay\model\RechargeData;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

class ApplePay
{
    public function index($server_id, $user_id, $recharge_id, $type, $old_server_id, $nChanneId)
    {

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

        $data['user_id'] = $user_id;
        $data['server_id'] = $server_id;
        $data['recharge_id'] = $recharge_id;
        $data['amount'] = $amount;
        $data['money'] = $money;
        $data['pay_type'] = 1;
        $data['order_id'] = 'ApplePay_' . $user_id . date('YmdHis');//渠道ID_+用户ID+日期、
        $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        $data['channel_id'] = $nChanneId;
        $data['pay_ip'] = request()->ip();
        $data['real_server_id'] = $old_server_id;
        if (RechargeData::insertGetId($data)) {
            return json(['code' => true, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '订单创建成功']);
        } else {
            return json(['code' => false, 'order_id' => $data['order_id'], 'amount' => $money, 'msg' => '订单创建失败']);
        }
    }


    /**
     * 获取苹果支付接收校验付款凭证
     * @param $receipt_data
     */
    public function validate_apple_pay()
    {
        $receipt = Request::param('receipt_data');
        $receipt = str_replace(array("\r\n", "\r", "\n", " "), "", $receipt);
        Log::write("apply pay validate receipt data:" . $receipt);

        $server_id = Request::param('server_id');
        $user_id = Request::param('user_id');
        $real_server_id = Request::param('old_server_id');
        $channel_id = Request::param('nChanneId');

        Log::write("validate apple pay param:【server_id】" . $server_id . "");
        Log::write("validate apple pay param:【user_id】" . $user_id . "");
        Log::write("validate apple pay param:【real_server_id】" . $real_server_id . "");
        Log::write("validate apple pay param:【channel_id】" . $channel_id . "");
        /**
         * Status    描述
         * 21000    App Store不能读取你提供的JSON对象
         * 21002    receipt-data域的数据有问题
         * 21003    receipt无法通过验证
         * 21004    提供的shared secret不匹配你账号中的shared secret
         * 21005    receipt服务器当前不可用
         * 21006    receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
         * 21007    receipt是Sandbox receipt，但却发送至生产系统的验证服务
         * 21008    receipt是生产receipt，但却发送至Sandbox环境的验证服务
         */

        if (strlen($receipt) < 20) {
            return [
                'status' => false,
                'message' => '参数错误!'
            ];
        }

        $html = $this->curl_func($receipt, true);
        Log::write("validate apple pay result html:" . $html);
        $data = json_decode($html, true);
        // 判断是否购买成功
        if (intval($data['status']) === 0) {

            /**
             *  {
             * "receipt": {
             * "receipt_type": "ProductionSandbox",
             * "adam_id": 0,
             * "app_item_id": 0,
             * "bundle_id": "com.main.yiwan.longtengtianxia.home",
             * "application_version": "1",
             * "download_id": 0,
             * "version_external_identifier": 0,
             * "receipt_creation_date": "2021-08-23 09:47:19 Etc/GMT",
             * "receipt_creation_date_ms": "1629712039000",
             * "receipt_creation_date_pst": "2021-08-23 02:47:19 America/Los_Angeles",
             * "request_date": "2021-08-23 09:47:56 Etc/GMT",
             * "request_date_ms": "1629712076415",
             * "request_date_pst": "2021-08-23 02:47:56 America/Los_Angeles",
             * "original_purchase_date": "2013-08-01 07:00:00 Etc/GMT",
             * "original_purchase_date_ms": "1375340400000",
             * "original_purchase_date_pst": "2013-08-01 00:00:00 America/Los_Angeles",
             * "original_application_version": "1.0",
             * "in_app": [
             * {
             * "quantity": "1",
             * "product_id": "1",
             * "transaction_id": "1000000864333522",
             * "original_transaction_id": "1000000864333522",
             * "purchase_date": "2021-08-23 09:47:19 Etc/GMT",
             * "purchase_date_ms": "1629712039000",
             * "purchase_date_pst": "2021-08-23 02:47:19 America/Los_Angeles",
             * "original_purchase_date": "2021-08-23 09:47:19 Etc/GMT",
             * "original_purchase_date_ms": "1629712039000",
             * "original_purchase_date_pst": "2021-08-23 02:47:19 America/Los_Angeles",
             * "is_trial_period": "false",
             * "in_app_ownership_type": "PURCHASED"
             * }
             * ]
             * },
             * "environment": "Sandbox",
             * "status": 0
             * }
             */

            $recharge_id = $data['receipt']['in_app'][0]['product_id'];
            $transaction_id = $data['receipt']['in_app'][0]['transaction_id'];
            $this->apple_recharge($server_id, $user_id, $recharge_id, $real_server_id, $channel_id, $transaction_id);
            $result = array(
                'status' => true,
                'message' => '购买成功'
            );

            Log::write("购买成功");

        } else {
            $result = array(
                'status' => false,
                'message' => '购买失败 status:' . $data['status']
            );
            Log::write("购买失败");
        }
        return json($result);
    }

    /**
     * 发送数据校验请求
     * @param $receipt_data     付款后凭证
     * @param bool $sandbox
     */
    public function curl_func($receipt_data, $sandbox = false)
    {
//        $POSTFIELDS = array("receipt-data" => $receipt_data, 'password' => self::SECRET);
//        $POSTFIELDS = json_encode($POSTFIELDS);

        $secret = "093df073cd1742ffbd512dedc5c79d50";
        $POSTFIELDS = '{"receipt-data":"' . $receipt_data . '","password":"' . $secret . '"}';

        $sandbox_url = "https://sandbox.itunes.apple.com/verifyReceipt";
        $buy_url = "https://buy.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $sandbox_url : $buy_url;

        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /**
     * 充值记录插入数据表
     * @param $server_id        当前服务器ID
     * @param $user_id          用户ID
     * @param $recharge_id      充值ID（product_id）
     * @param $old_server_id    原服务器ID
     * @param $channel_id       渠道ID
     * @param $transaction_id   苹果支付交易ID
     */
    public function apple_recharge($server_id, $user_id, $recharge_id, $old_server_id, $channel_id, $transaction_id)
    {
        $money = 0;//充值金额
        $amount = 0;//对应的充值道具数量
        if (isset($user_id)) {
            $money = $this->get_recharge_money($recharge_id);
            $money = isset($money) ? $money : 0;
            $amount = $money * 100;
        }

        $order_id = 'ApplePay_' . $user_id . date('YmdHis');//渠道ID_+用户ID+日期
        $data['user_id'] = $user_id;
        $data['server_id'] = $server_id;
        $data['recharge_id'] = $recharge_id;
        $data['money'] = $money;
        $data['amount'] = $amount;
        $data['pay_type'] = 1;
        $data['order_id'] = $order_id;
        $data['add_time'] = (new \DateTime())->format('Y-m-d H:i:s');
        $data['channel_id'] = $channel_id;
        $data['pay_ip'] = request()->ip();
        $data['real_server_id'] = $old_server_id;
        $data['remark'] = 'Apple Pay交易号【transaction_id】:' . $transaction_id;
        $data['order_status'] = 1;
        $insertId = RechargeData::insertGetId($data);
        Db::startTrans();
        try {
            if ($insertId) {
                $this->sync_recharge($order_id);

            } else {
                return json(['code' => false, 'order_id' => $order_id, 'amount' => $money, 'msg' => '充值订单交易失败!!!']);
            }
        } catch (Exception $exception) {
            Log::write('Apple Pay Sync RechargeData exception【' . $exception . '】');
            Db::rollback();
            echo 'fail';
            return false;
        }

        Db::commit();
    }

    /**
     * 同步充值数据
     * 添加对应道具
     * @param $order_id
     * @param $trade_no
     */
    public function sync_recharge($order_id)
    {
        $where[] = [
            ['order_id', '=', trim($order_id)],
        ];
        $info = RechargeData::where($where)->find();
        if (!$info) {
            Log::write("订单编号【" . $order_id . "】数据不存在或已处理！！！");
            return false;
        } else {
            Db::startTrans();
            try {
                $new_data['user_id'] = $info['user_id'];
                $new_data['server_id'] = $info['server_id'];
                $new_data['recharge_id'] = $info['recharge_id'];
                $new_data['pay_type'] = $info['pay_type'];
                $new_data['channel_id'] = $info['channel_id'];
                $new_data['add_time'] = $info['add_time'];
                $new_data['amount'] = $info['amount'];
                $new_data['remark'] = $info['remark'];
                $new_data['money'] = $info['money'];
                $new_data['pay_ip'] = $info['pay_ip'];
                $new_data['order_id'] = $info['order_id'];
                $new_data['real_server_id'] = $info['real_server_id'];

                $ret = Db::connect('db_config_main')->table('recharge_data')->insertGetId($new_data);
                if (!$ret) return json(['code' => -1, 'msg' => '充值记录写入失败', 'data' => $new_data]);

                $user_info = UserRechargeStatistics::where('user_id', '=', $info['user_id'])->find();
                if ($user_info) {
                    $user_recharge['user_id'] = $user_info['user_id'];
                    //判断数据表中的时间戳与当前时间戳的年份、月份是否一致
                    if (date('y-m', $user_info['update_time']) == date('y-m', time())) {
                        $user_recharge['month_recharge'] = $user_info['month_recharge'] + $info['money'];
                    } else {
                        $user_recharge['month_recharge'] = $info['money'];
                    }
                    $user_recharge['total_recharge'] = $user_info['total_recharge'] + $info['money'];
                    $user_recharge['update_time'] = time();
                    UserRechargeStatistics::update($user_recharge);
                } else {
                    $user_recharge['user_id'] = $info['user_id'];
                    $user_recharge['month_recharge'] = $info['money'];
                    $user_recharge['total_recharge'] = $info['money'];
                    $user_recharge['update_time'] = time();
                    UserRechargeStatistics::insert($user_recharge);
                }
                //充值元宝(命令发送服务器)
                test::webw_packet_recharge($info['server_id'], $ret);
                return true;
            } catch (Exception $exception) {
                Log::write('Apple Pay Sync RechargeData exception【' . $exception . '】');
                Db::rollback();
                echo 'fail';
                return false;
            }
            Db::commit();
        }
    }

    /**
     * 获取充值ID对应金额
     * @param $recharge_id
     * @return int
     */
    public function get_recharge_money($recharge_id)
    {
        $money = 0;
        switch ($recharge_id) {
            case 1:
                $money = 12;
                break;
            case 2:
                $money = 50;
                break;
            case 3:
                ;
                $money = 108;
                break;
            case 4:
                $money = 518;
                break;
            case 5:
                $money = 998;
                break;
            case 6:
                $money = 1998;
                break;
            case 7:
                $money = 2998;
                break;
            case 8:
                $money = 4998;
                break;
            default:
                break;
        }
        return $money;
    }

    /**
     * 充值退款
     */
    public function recharge_refund()
    {
        //TODO:获取返回参数处理
        $params = $_POST;

        $server_id = 0;
        $actor_id = 0;
        $order_id = '';
        $trade_no = '';
        $amount = 0;

        $where[] = [
            ['order_id', '=', trim($order_id)],
            ['user_id', '=', $actor_id],
            ['server_id', '=', $server_id]
        ];
        $order_info = \app\admin\model\RechargeData::where($where)->find();
        if ($order_info) {
            $ret = refund_record($server_id, $actor_id, $order_id, $trade_no, $amount);
            if ($ret > 0) {
                //充值退款发送服务器请求
                test::webw_packet_recharge_refund($ret, $server_id, $actor_id);
            }
        }
    }

    public function recharge_refund_test()
    {
        $server_id = 3;
        $actor_id = 4294977297;
        $order_id = 'HP_1288491189320210619101252';
        $trade_no = 'H21061934936971F';
        $money = 100;
        $amount = 10000;
//        $ret = \app\common\refund_record($server_id, $actor_id, $order_id, $trade_no,$money, $amount);
        $ret = $this->refund_record($server_id, $actor_id, $order_id, $trade_no, $money, $amount);
        if ($ret > 0) {
            //充值退款发送服务器请求
            test::webw_packet_recharge_refund($ret, $server_id, $actor_id);
        }
    }


    /**
     * 添加退款记录
     * @param $server_id    服务器ID
     * @param $user_id      用户ID（角色ID）
     * @param $order_id     订单编号
     * @param $trade_no     Apple Pay 交易号
     * @param $amount       订单金额
     */
    public function refund_record($server_id = null, $user_id = null, $order_id = null, $trade_no = null, $money = null, $amount = null)
    {
        $data['server_id'] = $server_id;
        $data['actor_id'] = $user_id;
        $data['order_id'] = $order_id;
        $data['trade_no'] = $trade_no;
        $data['amount'] = $amount;
        $data['money'] = $money;
        $data['status'] = 0; //默认未处理
        $data['create_time'] = time();
        return \app\admin\model\RefundData::insertGetId($data);
    }

    public function get_json_str()
    {
        $json_str = '{
	"receipt": {
		"receipt_type": "ProductionSandbox",
		"adam_id": 0,
		"app_item_id": 0,
		"bundle_id": "com.main.yiwan.longtengtianxia.home",
		"application_version": "1",
		"download_id": 0,
		"version_external_identifier": 0,
		"receipt_creation_date": "2021-08-24 01:52:21 Etc/GMT",
		"receipt_creation_date_ms": "1629769941000",
		"receipt_creation_date_pst": "2021-08-23 18:52:21 America/Los_Angeles",
		"request_date": "2021-08-24 01:52:26 Etc/GMT",
		"request_date_ms": "1629769946040",
		"request_date_pst": "2021-08-23 18:52:26 America/Los_Angeles",
		"original_purchase_date": "2013-08-01 07:00:00 Etc/GMT",
		"original_purchase_date_ms": "1375340400000",
		"original_purchase_date_pst": "2013-08-01 00:00:00 America/Los_Angeles",
		"original_application_version": "1.0",
		"in_app": [{
			"quantity": "1",
			"product_id": "2",
			"transaction_id": "1000000864767492",
			"original_transaction_id": "1000000864767492",
			"purchase_date": "2021-08-24 01:52:21 Etc/GMT",
			"purchase_date_ms": "1629769941000",
			"purchase_date_pst": "2021-08-23 18:52:21 America/Los_Angeles",
			"original_purchase_date": "2021-08-24 01:52:21 Etc/GMT",
			"original_purchase_date_ms": "1629769941000",
			"original_purchase_date_pst": "2021-08-23 18:52:21 America/Los_Angeles",
			"is_trial_period": "false",
			"in_app_ownership_type": "PURCHASED"
		}]
	},
	"environment": "Sandbox",
	"status": 0
}';
//      dump($json_str);
        echo PHP_EOL;
        $data = json_decode($json_str, true);

        var_dump($data['receipt']['in_app'][0]['product_id']);
    }
}