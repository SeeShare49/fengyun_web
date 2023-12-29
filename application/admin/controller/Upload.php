<?php
// +----------------------------------------------------------------------
// | HulaCWMS 呼啦企业网站管理系统
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://www.zhuopro.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 灼灼文化
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\facade\Log;

class Upload extends Base
{
    /**
     * @var string 错误信息
     */
    public $error = '';
    public $info;

    /**
     * 图片上传
     */
    public function picture()
    {
        $config = config('admin.PICTURE_UPLOAD');
        $re = $this->pic_video($config);
        if ($re) {
            $this->success("上传成功！", '', $this->info);
        } else {
            $this->error($this->error);
        }
    }

    /**
     * 视频上传
     */
    public function video()
    {
        $config = config('admin.VIDEO_UPLOAD');
        $re = $this->pic_video($config);
        if ($re) {
            $this->success("上传成功！", '', $this->info);
        } else {
            $this->error($this->error);
        }
    }

    /**
     * 文件附件上传
     */
    public function file()
    {
        $config = config('admin.FILE_UPLOAD');
        $re = $this->pic_video($config);
        if ($re) {
            $this->success("上传成功！", '', $this->info);
        } else {
            $this->error($this->error);
        }
    }

    /**
     * 图片和视频上传方法
     * @param $config
     * @param string $formFile
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pic_video($config, $formFile = 'file')
    {
        $rootPath = $config['rootPath'];
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($formFile);

        if (empty($file)) {
            $this->error = "上传失败！";
            return FALSE;
        }

        // 验证文件大小和文件类型
        $info = $file->validate(['size' => $config['maxSize'], 'ext' => $config['exts']]);
        //获取文件md5用以验证是否曾上传过。
        $fileMd5 = $info->md5();
        $fileSha1 = $info->sha1();
        $where[] = ['md5', '=', $fileMd5];
        $where[] = ['status', '>=', -1];

        $pic = db('picture')->where($where)->find();

        //如果上传过，直接从数据库中拉取图片地址。
        if ($pic) {
            $data['path'] = $pic['path'];
            $data['url'] = $pic['url'];
            $this->info = $data;
            return true;
        }


        //保存到站点目录下
        $info = $info->move($rootPath);

        if ($info) {
            $saveFileName = $info->getSaveName();
            unset($info);
            $savePath = $rootPath . '/' . str_replace('\\', '/', $saveFileName);

            //将文件信息保存到数据库中
            $data['path'] = '/' . stripslashes( $savePath);
            $data['url'] = '';
            $insertData['path'] = $data['path'];
            $insertData['url'] = $data['url'];
            $insertData['md5'] = $fileMd5;
            $insertData['sha1'] = $fileSha1;
            $insertData['create_time'] = time();
            db('picture')->insert($insertData);
            $this->info = $data;
            return true;
        } else {
            $this->error = "上传失败：" . $file->getError();
            return false;
        }
    }

}
