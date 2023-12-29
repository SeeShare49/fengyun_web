<?php

namespace app\welfare\controller;

use AlibabaCloud\SDK\Cdn\V20180510\Models\DescribeDomainRealTimeBpsDataResponseBody\data;
use app\admin\model\Welfare;
use app\admin\model\WelfareRecord;
use app\common\ServerManage;
use app\common\test;
use think\Controller;
use think\facade\Request;
use think\facade\View;

use app\welfare\validate\Welfare as WelfareValidate;

/**
 * 公众号每日福利领取
 **/
class Index extends Controller
{
    public function index()
    {
        $server_list = ServerManage::getServerList();
        if (Request::isPost()) {
            $data = $_POST;
            $validate = new WelfareValidate();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $server_id = $data['server_id'];
            $nickname = $data['nickname'];
            $actor_id = get_actor_id($nickname, $server_id);
            if (!isset($actor_id)) {
                $this->error('角色名称【' . $nickname . '】未匹配到对应的数据,请核实确认！');
            }
            $content = '玩家【' . $nickname . '】领取每日福利礼包！';
            $propInfo = Welfare::where('id', '=', 1)->value('content');
            $where[] = [
                ['welfare_id', '=', 1],
                ['actor_id', '=', trim($actor_id)]
            ];
            $record = WelfareRecord::where($where)->order('create_time desc')->limit(1)->find();
            if ($record) {
                if (date('Ymd', $record['create_time']) == date('Ymd')) {
                    $this->error('亲爱的玩家,您已领取每日福利,请勿重复提交！');
                }

                if (self::Record(1, $server_id, $actor_id, $nickname)) {
                    test::mail($server_id, $nickname, '每日福利领取', $content, $propInfo);
                    $this->success('每日福利领取已提交,请移步至游戏中领取奖励！');
                } else {
                    $this->error('每日福利领取提交失败,请重新提交！');
                }
            } else {
                if (self::Record(1, $server_id, $actor_id, $nickname)) {
                    test::mail($server_id, $nickname, '每日福利领取', $content, $propInfo);
                    $this->success('每日福利领取已提交,请移步至游戏中领取奖励！');
                } else {
                    $this->error('每日福利领取提交失败,请重新提交！');
                }
            }
        } else {
            View::assign([
                'server_list' => $server_list,
                'meta_title' => '提交信息,领取每日福利'
            ]);
            return View::fetch();
        }
    }

    /**
     * 记录每日福利领取记录
     * @param $welfare_id
     * @param $server_id
     * @param $actor_id
     * @param $player_name
     * @return bool
     * @throws \think\Exception
     */
    public static function Record($welfare_id, $server_id, $actor_id, $player_name)
    {
        $recordInfo['welfare_id'] = $welfare_id;
        $recordInfo['server_id'] = $server_id;
        $recordInfo['actor_id'] = $actor_id;
        $recordInfo['player_name'] = $player_name;
        $recordInfo['status'] = 1;
        $recordInfo['create_time'] = time();
        $ret = WelfareRecord::insert($recordInfo);
        return $ret ? true : false;
    }
}
