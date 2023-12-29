<?php

namespace app\admin\controller;

use AlibabaCloud\SDK\Cdn\V20180510\Cdn;
use AlibabaCloud\SDK\Cdn\V20180510\Models\PushObjectCacheRequest;
use AlibabaCloud\SDK\Cdn\V20180510\Models\RefreshObjectCachesRequest;
use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;
use app\admin\model\PreheatSource;
use app\admin\model\PropCsv;
use app\admin\model\UploadFileRecord;
use app\common\CsvManage;
use think\facade\Log;
use think\facade\View;

define('ALIYUNCDNURL', 'https://cdn.aliyuncs.com/?');
define('ACCESS_KEY_ID', config('admin.ACCESS_KEY_ID'));
define('ACCESS_KEY_SECRET', config('admin.ACCESS_KEY_SECRET'));


/**
 * 客户端文件上传更新
 *
 */
class ClientFileUpdate extends Base
{
    public function index()
    {
        $name = trim(input('file_name'));
        $where[] = ['1', '=', 1];
        if ($name) {
            $where[] = ['file_name', 'like', "%$name%"];
        }

        $lists = UploadFileRecord::where($where)
            ->order('upload_time desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'file_name' => $name,
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'page' => $page,
            'meta_title' => '客户端上传更新文件记录列表'
        ]);
        return $this->fetch();
    }

    /**
     * 客户端上传更新文件
     */
    public function upload()
    {
        if (request()->isPost()) {
            $channel = $_POST['channel_id'];
            $path = '../public/client_update_file/qd' . $channel . '/download';
            if (!file_exists($path)) {
                //默认的 mode 是 0777，意味着最大可能的访问权。
                mkdir($path, 0777, true);
            }
            $tmpname = $_FILES['propfile']['tmp_name'];
            $filename = $_FILES['propfile']['name'];
            $file = $path . '/' . $filename;

            //$expand_name = explode(".", $filename);

            $pathinfo = pathinfo($filename);

            if (empty($tmpname)) {
                $this->error('请选择上传文件');
            }

            if (empty($file)) {
                $this->error('请选择上传文件');
            }
            $extension = "$pathinfo[extension]";
            $basename = "$pathinfo[basename]";

            if (strtolower($extension) == "zip" || strtolower($extension) == "txt") {
                if (move_uploaded_file($tmpname, $file)) {
                    $data['file_name'] = $basename;
                    $data['file_ext'] = $extension;
                    $data['admin_name'] = USERNAME;
                    $data['channel_id'] = $channel;
                    $data['upload_time'] = time();
                    //$data['file_size']  = filesize($basename);

                    //fclose($this);

                    if (!UploadFileRecord::insert($data)) {
                        Log::write("客户端文件更新记录失败!");
                    }

                    $resData = [
                        'code' => 1,
                        'msg' => '客户端文件' . $filename . '上传成功！',
                        'data' => '',
                        'wait' => 2,
                        'url' => '/index'
                    ];

                    /**  阿里云预热刷新  **/
                    $this->preheat_refresh($channel);

                    return json($resData);
                    //fclose($file); // 关闭指针

                } else {
                    $this->error('文件上传失败！');
                }
            } else {
                $resData = [
                    'code' => 0,
                    'msg' => '上传文件格式仅限于zip或txt格式文件！',
                    'data' => '',
                    'wait' => 2,
                    'url' => '/upload'
                ];
                return json($resData);
                //$this->success('上传文件格式仅限于zip或txt格式文件！');
            }
        } else {
            $channel_id = trim(input('channel_id'));
            $channel_list = \app\admin\model\Channel::select();
            $this->assign([
                'channel_id' => $channel_id,
                'meta_title' => '客户端更新文件上传',
                'channel_list' => $channel_list
            ]);
            return $this->fetch();
        }
    }

    /**
     * 清空道具
     */
    public function clear()
    {
        $res = UploadFileRecord::where('1=1')->delete(true);
        if ($res !== false) {
            $this->success('客户端更新文件记录清空成功！', '');
        } else {
            $this->error('客户端更新文件记录清空失败！');
        }
    }

    /**
     * 客户端文件更新，同步刷新cdn
     * @param $channel_id 渠道ID
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function preheat_refresh($channel_id)
    {
        $source_url = 'http://dl.52yiwan.cn/client_update_file/qd' . $channel_id . '/download/';

        $client = self::createClient(ACCESS_KEY_ID, ACCESS_KEY_SECRET);

        $pushObjectCacheRequest = new PushObjectCacheRequest([
            "objectPath" => $source_url
        ]);

        $resp = $client->pushObjectCache($pushObjectCacheRequest);


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

}
