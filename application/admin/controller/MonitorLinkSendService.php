<?php

namespace app\admin\controller;

use think\facade\Log;
use think\facade\Request;
use think\facade\View;


/**
 * 监测链接发送服务(接收的数据)
 */
class MonitorLinkSendService extends Base
{
    public function index()
    {
        $where[] = ['1', '=', 1];

        $search = trim(input('search'));
        if ($search) {
            $where[] = ['AID_NAME', 'like', $search];
        }

        $lists = \app\admin\model\MonitorLinkSendService::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'page' => $page,
            'meta_title' => '接收监测链接发送服务信息列表'
        ]);
        return $this->fetch();
    }


    /**
     * 接收数据入口
     **/
    public function receive()
    {
        //https://xxxx.xxx.com?aid=AID&cid=CID&cid_name=CID_NAME&imei=IMEI&mac=MAC&oaid=OAID&androidid=ANDROIDID&os=OS&TIMESTAMP=TS&callback_url=__CALLBACK_URL__
        $params = Request::param();
        Log::write("巨量引擎广告接收参数:" . $params);
        $data['AID'] = $params['aid'];
        $data['AID_NAME'] = $params['aid_name'];
        $data['ADVERTISER_ID'] = $params['advertiser_id'];
        $data['CID'] = $params['cid'];
        $data['CID_NAME'] = $params['cid_name'];
        $data['CAMPAIGN_ID'] = $params['campaign_id'];
        $data['CAMPAIGN_NAME'] = $params['campaign_name'];
        $data['CTYPE'] = $params['ctype'];
        $data['CSITE'] = $params['csite'];
        $data['CONVERT_ID'] = $params['convert_id'];
        $data['REQUEST_ID'] = $params['request_id'];
        $data['OS'] = $params['os'];
        $data['IMEI'] = $params['imei'];
        $data['ANDROIDID'] = $params['android_id'];
        $data['TS'] = $params['TIMESTAMP'];
        $data['CALLBACK_URL'] = $params['callback_url'];
        $data['CALLBACK_PARAM'] = $params['callback_param'];
        $data['MODEL'] = $params['model'];
        $data['UNION_SITE'] = $params['union_site'];

        if (\app\admin\model\MonitorLinkSendService::insert($data)) {
            Log::write("巨量引擎广告数据插入成功!");
        } else {
            Log::write("巨量引擎广告数据插入失败!");
        }

        return View::fetch('/index');
    }
}
