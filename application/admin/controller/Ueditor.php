<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\controller\Upload;

class Ueditor extends Controller
{

    //ueditor的配置文件
    private $configpath = './theme/admin/lib/ueditor/php/config.json';

    public function index()
    {
        $this->type = input('edit_type', '');
        //header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($this->configpath)), true);
        $this->config = $CONFIG;

        $action = input('action');
        switch ($action) {
            case 'config':
                $result = json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = $this->picture();
                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':

                break;
            /* 上传视频 */
            case 'uploadvideo':
                $result = $this->video();
                break;
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->file();
                break;

            /* 列出图片 */
            case 'listimage':
                /* 列出文件 */
            case 'listfile':
                $result = $this->_list($action);
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                break;
            default:
                $result = json_encode(array('state' => '请求地址出错'));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"]) && false) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            exit($result);
        }
    }

    //上传图片
    private function picture()
    {
        $title = '';
        $url = '';

        $upload = new Upload();
        $config = config('PICTURE_UPLOAD');
        $re = $upload->pic_video($config, 'upfile');
        if ($re) {
            $state = 'SUCCESS';
            $url = $upload->info['url'] ? $upload->info['url'] : $upload->info['path'];
        } else {
            $state = $upload->error;
        }

        $response = array(
            "state" => $state,
            "url" => $url,
            "title" => $title,
            "original" => $title,
        );
        return json_encode($response);
    }

    //上传视频
    private function video()
    {
        $title = '';
        $url = '';

        $upload = new Upload();
        $config = config('VIDEO_UPLOAD');
        $re = $upload->pic_video($config, 'upfile');
        if ($re) {
            $state = 'SUCCESS';
            $url = $upload->info['url'] ? $upload->info['url'] : $upload->info['path'];
        } else {
            $state = $upload->error;
        }

        $response = array(
            "state" => $state,
            "url" => $url,
            "title" => $title,
            "original" => $title,
        );
        return json_encode($response);
    }

    //上传文件
    private function file()
    {
        $title = '';
        $url = '';

        $upload = new Upload();
        $config = config('FILE_UPLOAD');
        $re = $upload->pic_video($config, 'upfile');
        if ($re) {
            $state = 'SUCCESS';
            $url = $upload->info['url'] ? $upload->info['url'] : $upload->info['path'];
        } else {
            $state = $upload->error;
        }

        $response = array(
            "state" => $state,
            "url" => $url,
            "title" => $title,
            "original" => $title,
        );
        return json_encode($response);
    }


    private function _list($action)
    {
        /* 判断类型 */
        switch ($action) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $this->config['fileManagerAllowFiles'];
                $listSize = $this->config['fileManagerListSize'];
                $prefix = '/';
                $path = config('FILE_UPLOAD.rootPath');
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $this->config['imageManagerAllowFiles'];
                $listSize = $this->config['imageManagerListSize'];
                $prefix = '/';
                $path = config('PICTURE_UPLOAD.rootPath');
        }
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = intval($start) + intval($size);

        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }
        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
            $list[] = $files[$i];
        }
        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));
        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param string $path
     * @param string $allowFiles
     * @param array $files
     * @return array
     */
    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = array(
                            'url' => '/' . $path2,
                            'mtime' => filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }

}