<?php

namespace app\admin\controller;

use app\common;

class Fragment extends Base
{
    public function upload()
    {
        if (request()->isPost()) {
            //实例化并获取系统变量传参
            $upload = new common\FragmentUpload($_FILES['file']['tmp_name'], $_POST['blob_num'], $_POST['total_blob_num'], $_POST['file_name']);
//            $upload = new common\FragmentUpload($_FILES['file']['tmp_name'], $_POST['blob_num'], $_POST['total_blob_num'], $_FILES['file']['name']);

            //调用方法，返回结果
            $upload->apiReturn();
        } else {
            $this->meta_title = '上传更新文件';
            return $this->fetch();
        }
    }
}

