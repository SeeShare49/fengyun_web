<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/17
 * Time: 16:44
 */

namespace app\common;

class FragmentUpload
{
    private $filepath = '../public/upload/client_u_file/'; //上传目录
    private $tmpPath;  //PHP文件临时目录
    private $blobNum; //第几个文件块
    private $totalBlobNum; //文件块总数
    private $fileName; //文件名

    public function __construct($tmpPath, $blobNum, $totalBlobNum, $fileName)
    {
        $this->tmpPath = $tmpPath;
        $this->blobNum = $blobNum;
        $this->totalBlobNum = $totalBlobNum;
        $this->fileName = $fileName;

        $this->moveFile();
        $this->fileMerge();
    }

    //判断是否是最后一块，如果是则进行文件合成并且删除文件块
    private function fileMerge()
    {
        if ($this->blobNum == $this->totalBlobNum) {
            $blob = '';
            for ($i = 1; $i <= $this->totalBlobNum; $i++) {
                $blob .= file_get_contents($this->filepath . '/' . $this->fileName . '__' . $i);
            }
            file_put_contents($this->filepath . '/' . $this->fileName, $blob);
            $this->deleteFileBlob();
        }
    }

    //删除文件块
    private function deleteFileBlob()
    {
        for ($i = 1; $i <= $this->totalBlobNum; $i++) {
            @unlink($this->filepath . '/' . $this->fileName . '__' . $i);
        }
    }

    //移动文件
    private function moveFile()
    {
        $this->touchDir();
        $filename = $this->filepath . '/' . $this->fileName . '__' . $this->blobNum;
        move_uploaded_file($this->tmpPath, $filename);
    }

    //API返回数据
    public function apiReturn()
    {
        if ($this->blobNum == $this->totalBlobNum) {
            if (file_exists($this->filepath . '/' . $this->fileName)) {
                $data['code'] = 2;
                $data['msg'] = 'success';
                $data['file_path'] = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['DOCUMENT_URI']) . str_replace('.', '', $this->filepath) . '/' . $this->fileName;
            }
        } else {
            if (file_exists($this->filepath . '/' . $this->fileName . '__' . $this->blobNum)) {
                $data['code'] = 1;
                $data['msg'] = 'waiting for all';
                $data['file_path'] = '';
            }
        }
        header('Content-type: application/json');
        echo json_encode($data);
    }

    //建立上传文件夹
    private function touchDir(){
        if(!file_exists($this->filepath)){
            return mkdir($this->filepath);
        }
    }
}