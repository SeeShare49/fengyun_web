<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/14
 * Time: 10:32
 */


namespace app\admin\controller;

//set_time_limit(0);

use app\common;

use app\admin\model\UpdateFile;

use Log;

class Client extends Base
{
    public function index()
    {
        $version = trim(input('version'));
        $where[] = ['1', '=', 1];
        if ($version) {
            $where[] = ['version', 'like', "%$version%"];
        }
        $lists =UpdateFile::where($where)
            ->order('id desc')
            ->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists'=> $lists,
            'page'=>$page,
            'version'=>$version,
            'empty'=>'<td class="empty" colspan="5">暂无数据</td>',
            'meta_title' => '更新文件版本'
        ]);
        return $this->fetch();
    }

    /**
     * 文件上传
     */
    public function upload()
    {
        if (request()->isPost()) {
            //$dir = getcwd();
            // $path = $dir . '/upload/client_u_file/';
            $path = '../public/upload/client_u_file/';
            if (!file_exists($path)) {
                //默认的 mode 是 0777，意味着最大可能的访问权。
                mkdir($path, 0777, true);
            }
            ini_set("memory_limit", "2048");

            /** 临时文件名 @var TYPE_NAME $tmpname */
            $tmpname = $_FILES['file']['tmp_name'];
            $filename = $_FILES['file']['name'];
            //获取当前目录的绝对路径
            $filepath = $path . '/' . $filename;
            Log::write("上传文件大小:" . $_FILES['file']['size']);
            Log::write("上传文件错误信息:" . $_FILES['file']['error']);
            $arr = explode('.', $filename);
            if ($arr[1] == "zip") {
                if (move_uploaded_file($tmpname, $filepath)) {
                    $z = new Unzip();
                    $z->unzip($filepath, $path, true, true);
                    // $z = Unzip::unzip($filepath, $path, true, false);

                    @unlink($filepath);
                    $result['status'] = 1;
                    $result['message'] = "文件上传成功";

                    $data = $_POST;
                    $data['user_id'] = UID;
                    $data['upload_file_name']  =$filename;
                    $data['upload_time'] = time();
                    db('update_file')->insert($data);

                    action_log('client_upload_file','client',null,UID);
                    $this->success("文件上传成功", "client/index");

                } else {
                    $result['status'] = 0;
                    $result['message'] = "文件上传失败";
                    $this->error("文件上传失败");
                }
            } else {
                $this->error("上传压缩包格式错误,请上传zip压缩文件!");
            }
        } else {
            $this->assign([
                'meta_title'=>'上传更新文件'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 清空上传更新日志
     */
    public function clear()
    {
        $res = db('update_file')->where('1=1')->delete();
        if ($res !== false) {
            action_log('client_clear_update_file','client',null,UID);
            $this->success('上传更新日志清空成功！', '');
        } else {
            $this->error('上传更新日志清空失败！');
        }
    }
}