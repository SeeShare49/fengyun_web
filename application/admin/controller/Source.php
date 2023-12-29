<?php

namespace app\admin\controller;

use AlibabaCloud\SDK\Cdn\V20180510\Cdn;
use AlibabaCloud\SDK\Cdn\V20180510\Models\PushObjectCacheRequest;
use AlibabaCloud\SDK\Cdn\V20180510\Models\RefreshObjectCachesRequest;
use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;
use app\admin\model\PreheatSource;
use app\admin\validate\Preheatsource as SourceValidate;

use app\common\ChannelManage;
use app\common\HttpSend;
use Log;
use think\facade\View;

$path = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($path)) {
    require_once $path;
}


define('ALIYUNCDNURL', 'https://cdn.aliyuncs.com/?');
define('ACCESS_KEY_ID', config('admin.ACCESS_KEY_ID'));
define('ACCESS_KEY_SECRET', config('admin.ACCESS_KEY_SECRET'));

class Source extends Base
{
    /**
     * 显示预热资源列表
     */
    public function index()
    {
        $where[] = ['status', '>', -1];
        $lists = PreheatSource::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign('lists', $lists);
        $this->assign('page', $page);
        $this->assign('empty', '<td class="empty" colspan="7">暂无数据</td>');

        $this->meta_title = '阿里云预热记录';
        return $this->fetch();
    }

    /**
     * 创建提交预热资源
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = $_POST;

            if ($data['channel_id'] == 0) {
                $this->error('请选择渠道进行资源预热！！！');
            }

            //http://dl.52yiwan.cn/client_update_file/qd12/download/

            $source_url = 'http://dl.52yiwan.cn/client_update_file/qd' . $data['channel_id'] . '/download/';

            $client = self::createClient(ACCESS_KEY_ID, ACCESS_KEY_SECRET);

            $pushObjectCacheRequest = new PushObjectCacheRequest([
                "objectPath" => $source_url
            ]);

            $resp = $client->pushObjectCache($pushObjectCacheRequest);

            /**
             * {
             * "headers": {
             * "Date": ["Sat, 17 Apr 2021 02:51:22 GMT"],
             * "Content-Type": ["application\/json;charset=utf-8"],
             * "Content-Length": ["79"],
             * "Connection": ["keep-alive"],
             * "Access-Control-Allow-Origin": ["*"],
             * "Access-Control-Allow-Methods": ["POST, GET, OPTIONS"],
             * "Access-Control-Allow-Headers": ["X-Requested-With, X-Sequence, _aop_secret, _aop_signature"],
             * "Access-Control-Max-Age": ["172800"],
             * "x-acs-request-id": ["A2BC94F4-3F92-41CC-A1F6-535060A47871"]
             * },
             * "body": {
             * "RequestId": "A2BC94F4-3F92-41CC-A1F6-535060A47871",
             * "PushTaskId": "12719676361"
             * }
             * }
             **/


            $resp_data = json_decode(Utils::toJSONString(Tea::merge($resp)), true);// Utils::toJSONString(Tea::merge($resp));

            if (isset($resp_data['body'])) {
                $source_data['task_id'] = $resp_data['body']['PushTaskId'];
                $source_data['request_id'] = $resp_data['body']['RequestId'];
                $source_data['create_time'] = time();
                $source_data['refresh_time'] = 0;
                $source_data['status'] = 1;
                $re = PreheatSource::insertGetId($source_data);
                if ($re) {
                    action_log("preheat_source_add", "preheat_source", $re, UID);

                    //阿里云刷新目录或文件
                    /*  URL刷新开始 */
                    $client = self::createClient(ACCESS_KEY_ID, ACCESS_KEY_SECRET);
                    $refreshObjectCachesRequest = new RefreshObjectCachesRequest([
                        "objectPath" => $source_url
                    ]);
                    $refresh_result = $client->refreshObjectCaches($refreshObjectCachesRequest);
                    /*  URL刷新结束 */

                    /*  目录刷新 */
                    $client_dir = self::createClient(ACCESS_KEY_ID, ACCESS_KEY_SECRET);
                    $refreshObjectDirCachesRequest = new RefreshObjectCachesRequest([
                        "objectType" => "Directory",
                        "objectPath" => $source_url
                    ]);
                    $client_dir->refreshObjectCaches($refreshObjectDirCachesRequest);
                    /*  目录刷新 */


                    $refresh_result_data = json_decode(Utils::toJSONString(Tea::merge($refresh_result)), true);
                    if (isset($refresh_result_data)) {
                        if (PreheatSource::update(['status' => 2, 'refresh_time' => time(), 'id' => $re])) {
                            $resData = [
                                'code' => 1,
                                'msg' => '预热资源刷新成功！',
                                'data' => '',
                                'wait' => 2
                            ];
                            return json($resData);
                        } else {
//                            $this->error("刷新资源失败!!!");
                            $resData = [
                                'code' => 0,
                                'msg' => '刷新资源失败！',
                                'data' => '',
                                'wait' => 2
                            ];
                            return json($resData);
                        }
                    } else {
                        $resData = [
                            'code' => 0,
                            'msg' => '刷新资源错误！',
                            'data' => '',
                            'wait' => 2
                        ];
                        return json($resData);
                    }
                } else {
                    $resData = [
                        'code' => 0,
                        'msg' => '阿里云预热目录或资源失败！',
                        'data' => '',
                        'wait' => 2
                    ];
                    return json($resData);
                }
            }

        } else {
            $channel_list = ChannelManage::getChannelList();
            View::assign([
                'channel_list' => $channel_list,
                'meta_title' => '预热原站内容到缓存节点'
            ]);
            return $this->fetch();
        }
    }


    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Cdn Client
     */
    public static function createClient($accessKeyId, $accessKeySecret)
    {
        $config = new \Darabonba\OpenApi\Models\Config([
            "accessKeyId" => $accessKeyId,
            "accessKeySecret" => $accessKeySecret
        ]);

        // 访问的域名
        $config->endpoint = "cdn.aliyuncs.com";
        return new Cdn($config);
    }


    /**
     * 刷新阿里云节点上的文件内容
     * @param $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function refresh($id)
    {
        $info = PreheatSource::find($id);
        if (!$info) {
            $this->error('预热资源信息不存在或已删除！');
        }
        //$url = "https://cdn.aliyuncs.com?&";
        $refresh_data = "Action=RefreshObjectCaches&ObjectPath={$info['source_url']}&ObjectType=File";
        $refresh_send = \HttpSend::send_request(ALIYUNCDNURL, $refresh_data);
        if (!empty($refresh_send)) {
            $refresh_arr = json_decode($refresh_send, true);
            if (isset($refresh_arr['Code'])) {
                $data['refresh_error_msg'] = $refresh_arr['Message'];
                $data['status'] = 2;//刷新失败
            } else {
                $data['status'] = 3;//刷新成功
            }
            //TODO:判断ret_send  执行以下操作
            $data['id'] = $id;
            $data['refresh_time'] = time();
            $re = PreheatSource::update($data);
            if ($re) {
                action_log("preheat_source_refresh", "preheat_source", $re, UID);
                $this->success("预热资源刷新成功!", 'source/index');
            } else {
                $this->error("预热资源刷新失败!");
            }
        }
    }

    //删除阿里云预热记录
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $res = PreheatSource::delete($ids);
        if ($res) {
            //添加行为记录
            action_log("source_del", "source", $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 清空阿里云预热记录
     */
    public function clear()
    {
        $res = PreheatSource::delete(true);
        if ($res !== false) {
            action_log("source_clear", "source", null, UID);
            $this->success('清空预热记录成功！', '');
        } else {
            $this->error('清空预热记录失败！');
        }
    }
}
