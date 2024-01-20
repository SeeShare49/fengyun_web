<?php

namespace app\admin\controller;

use think\Controller;

class Dump extends Controller
{
    private $dbhost = '192.168.1.153'; //数据库主机名
    private $dbuser = 'root'; //数据库用户名
    private $dbpass = '123456'; //数据库密码
    private $dbport = 3306; //数据库端口
    private $database = "yw_db";
    private $charset = "utf8";
    private $sqlfile ="../public/databack/cq_game.sql";


    public function index()
    {
        $db = new \app\common\DbManage($this->dbhost, $this->dbuser, $this->dbpass, $this->database, $this->dbport, $this->charset);
        $db->restore($this->sqlfile);
    }
}
