<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/18
 * Time: 14:39
 */

namespace app\common;

use Log;

class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host = "192.168.1.153", $port = 22)
    {
        Log::write('coming __construct');
        $this->connection = ssh2_connect($host, $port);
        Log::write($this->connection);
        if (!$this->connection) {
            Log::write("Could not connect to $host on port $port.");
            throw new \Exception("Could not connect to $host on port $port.");
        }
    }

    public function login($username, $password)
    {
        if (!@ssh2_auth_password($this->connection, $username, $password)) {
            throw new \Exception("Could not authenticate with username $username " . "and password $password.");
        } else {
            Log::write('login success...');
        }
        $this->sftp = @ssh2_sftp($this->connection);
        if (!$this->sftp) {
            throw new \Exception("Could not initialize SFTP subsystem.");
        } else {
            Log::write("sftp:" . $this->sftp);
        }
    }

    public function uploadFile($local_file, $remote_file)
    {
        $sftp = $this->sftp;

        $stream = @fopen("ssh2.sftp://$sftp$remote_file", 'w');
        dump("stream:" . $stream);
        if (!$stream) {
            throw new \Exception("Could not open file: $remote_file");
        } else {
            \think\facade\Log::write("stream:" . $stream);
        }

        $data_to_send = @file_get_contents($local_file);
        //var_dump("data_to_send:".$data_to_send);
        if ($data_to_send === false) {
            throw new \Exception("Could not open local file: $local_file.");
        } else {
            \think\facade\Log::write('data send success!!!');
        }

        if (@fwrite($stream, $data_to_send) === false) {
            throw new \Exception("Could not send data from file: $local_file.");
        } else {
            $path = '../upload/client_u_file/';
            $z = new Unzip();
            echo "unzip:" . $z;
            $z->unzip($remote_file, $path, true, true);
            // $z = Unzip::unzip($filepath, $path, true, false);

            @unlink($remote_file);
            $result['status'] = 1;
            $result['message'] = "文件上传成功";
//
//            $data = $_POST;
//            $data['user_id'] = UID;
//            $data['upload_time'] = time();
//            db('update_file')->insert($data);

            // $this->success("文件上传成功", "client/index");
            \think\facade\Log::write('fwrite success!!!');
        }
        $this->releaseMemory();
        @fclose($stream);
    }


    public function releaseMemory()
    {
        $s = str_repeat('1', 1024);
        $p = &$s;
        $m = memory_get_usage();
        unset($s);  //销毁$s
        unset($p);
        $mm = memory_get_usage();
        // echo $p.'<br />';

        //echo "release memory total:" . intval($m) - intval($mm);
    }
}