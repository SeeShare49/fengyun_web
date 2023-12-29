<?php

namespace app\admin\controller;


use app\admin\model\PurchaseData;
use app\admin\validate\Mail as MailValidate;
use app\common\test;
use app\pay\model\RechargeData;
use app\pay\model\UserRechargeStatistics;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

class LoseOrder extends Base
{
    public function index()
    {
        $where[] = ['1', '=', 1];
        $order_id = trim(input('order_id'));
        if ($order_id) {
            $where[] = ['order_id', '=', trim($order_id)];
        }
        $recharge_type = trim(input('recharge_type'));
        if (!empty($recharge_type) && $recharge_type != -1) {
            $where[] = ['recharge_type', '=', $recharge_type];
        }

        $this->assign('order_id', $order_id);
        $lists = \app\admin\model\LoseOrder::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'order_id' => $order_id,
            'recharge_type' => $recharge_type,
            'empty' => '<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '充值订单补录信息'
        ]);
        return $this->fetch();
    }

    /**
     * 订单补录
     */
    public function create()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $validate = new \app\admin\validate\LoseOrder();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            try {
                $order_id = $data['order_id'];
                $recharge_type = trim($data['recharge_type']);
                $info = '';
                if (isset($recharge_type)) {
                    if ($recharge_type == 1) {
                        //游戏内充值
                        $info = $this->get_recharge_data($order_id);
                    } else {
                        //平台直充
                        $info = $this->get_purchase_data($order_id);
                    }
                    if (!$info) {
                        $this->error('订单编号【' . $data['order_id'] . '】不符合补录条件！');
                    }
                } else {
                    $this->error('补录类型【' . $recharge_type . '】有误,请核对信息!');
                }

                //启动事务
                Db::startTrans();

                /* 直充数据写入到Main库直充数据表或直充数据表 */
                $ret = $this->save_order_data($info, $recharge_type);

                //更新用户充值统计
                $this->update_user_recharge_statistics($info['user_id'], $info['money']);

                //修改game_data库游戏充值表或直充表数据状态
                $this->update_data_status($order_id, $recharge_type);

                //补录订单数据
                $this->save_lose_order_data($order_id, $recharge_type);

                if ($recharge_type == 1) {
                    //充值元宝(命令发送服务器)
                    test::webw_packet_recharge($info['server_id'], $ret);
                } else {
                    //平台直充(命令发送服务器)
                    test::webw_packet_purchase($info['server_id'], $ret);
                }
                // 提交事务
                Db::commit();
                $this->success('充值订单补录成功!');

            } catch (Exception $exception) {
                Log::write('玩家订单补录事务回滚,exception:' . $exception);
                Db::rollback();
            }
        } else {
            View::assign([
                'meta_title' => '充值订单补录'
            ]);
            return View::fetch();
        }
    }

    /**
     * 保存补录订单数据
     * @param $order_id         订单编号
     * @param $recharge_type    补录类型
     */
    public function save_lose_order_data($order_id, $recharge_type)
    {
        $data['order_id'] = $order_id;
        $data['admin_id'] = UID;
        $data['recharge_type'] = $recharge_type;
        $data['create_time'] = time();
        $ret = \app\admin\model\LoseOrder::insert($data);
        return (bool)$ret;
    }

    /**
     * 根据订单编号查询平台直充数据信息
     * @param $order_id     订单编号
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_purchase_data($order_id)
    {
        $param['order_id'] = $order_id;
        $param['order_status'] = 0;
        return \app\admin\model\PurchaseData::where($param)->find();
    }

    /**
     * 根据订单编号获取游戏充值数据信息
     * @param $order_id     订单编号
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_recharge_data($order_id)
    {
        $param['order_id'] = $order_id;
        $param['order_status'] = 0;
        return RechargeData::where($param)->find();
    }

    /**
     * 保存补录订单数据
     * @param $data             订单数据对象
     * @param $recharge_type    补录类型（1、游戏充值；2平台直充）
     * @return \think\response\Json|void
     * @throws Exception
     */
    public function save_order_data($data, $recharge_type)
    {
        $new_data['user_id'] = $data['user_id'];
        $new_data['server_id'] = $data['server_id'];
        $new_data['recharge_id'] = $data['recharge_id'];
        $new_data['pay_type'] = $data['pay_type'];
        $new_data['channel_id'] = $data['channel_id'];
        $new_data['add_time'] = $data['add_time'];
        $new_data['amount'] = $data['amount'];
        $new_data['remark'] = '订单补录时间:【' . date('Y-m-d H:i:s', time()) . '】';
        $new_data['money'] = $data['money'];
        $new_data['pay_ip'] = $data['pay_ip'];
        $new_data['order_id'] = $data['order_id'];
        $new_data['real_server_id'] = $data['real_server_id'];

        if ($recharge_type == 1) {
            $ret = Db::connect('db_config_main')->table('recharge_data')->insertGetId($new_data);
            if (!$ret) return json(['code' => -1, 'msg' => '补录充值记录写入失败', 'data' => $new_data]);
            return $ret;
        } else {
            $new_data['purchase_name'] = $data['purchase_name'];
            $ret = Db::connect('db_config_main')->table('purchase_data')->insertGetId($new_data);
            if (!$ret) {
                Log::write("补录直充订单号【{$data['order_id']}】写入失败");
                return json(['code' => -1, 'msg' => '补录直充订单写入失败', 'data' => $new_data]);
            }
            return $ret;
        }
    }

    /*
     * 更新玩家充值统计数据
     * @param $user_id      用户ID
     */
    public function update_user_recharge_statistics($user_id, $money)
    {
        if (isset($user_id)) {
            $statisticsInfo = UserRechargeStatistics::where('user_id', '=', $user_id)->find();
            $data['user_id'] = $user_id;
            if ($statisticsInfo) {
                /* 判断数据表中的时间戳与当前时间戳的年份、月份是否一致 */
                if (date('y-m', $statisticsInfo['update_time']) == date('y-m', time())) {
                    $data['month_recharge'] = $statisticsInfo['month_recharge'] + $money;
                } else {
                    $data['month_recharge'] = $money;
                }
                $data['total_recharge'] = $statisticsInfo['total_recharge'] + $money;
                $data['update_time'] = time();
                $ret = UserRechargeStatistics::update($data);
                if (!$ret) {
                    Log::write("用户ID【${user_id}】,数据充值统计更新金额【${money}】失败!");
                    return false;
                }
            } else {
                $data['month_recharge'] = $money;
                $data['total_recharge'] = $money;
                $data['update_time'] = time();
                $ret = UserRechargeStatistics::insert($data);
                if (!$ret) {
                    Log::write("用户ID【{$user_id}】,数据充值统计插入金额【{$money}】失败!");
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /*
     * 修改直充订单状态信息
     * @param $order_id       直充订单表编号ID
     * @param $remark   直充订单备注信息
    */
    public function update_data_status($order_id, $recharge_type)
    {
        $data['order_status'] = 1;
        if ($recharge_type == 1) {
            $ret = RechargeData::where('order_id', '=', $order_id)->update($data);
            if (!$ret) {
                Log::write("游戏充值订单编号【{$order_id}】状态修改失败!");
                return false;
            }
        } else {
            $ret = PurchaseData::where('order_id', '=', $order_id)->update($data);
            if (!$ret) {
                Log::write("直充订单编号【{$order_id}】状态修改失败!");
                return false;
            }
        }
        return true;
    }
}
