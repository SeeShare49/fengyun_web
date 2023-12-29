<?php

namespace app\admin\controller;

use app\admin\model\KuafuServerList as CrossServerModel;
use app\admin\validate\KuafuServer as ServerValidate;
use app\common\DbManage;
use think\Db;
use think\facade\Log;
use think\facade\Request;

define('DB_HOST', config('admin.DB_HOST'));
define('DB_USER', config('admin.DB_USER'));
define('DB_PASS', config('admin.DB_PASS'));

class KuafuServer extends Base
{
    private $dbhost = DB_HOST;
    private $dbuser = DB_USER;
    private $dbpass = DB_PASS;
    private $charset = "utf8mb4";
    protected $sqlfile = "../public/databack/cq_game.sql";
    protected $db_prefix = "cq_kf_game";

    /**
     * 跨服列表
     */
    public function index()
    {
        $servername = trim(input('servername'));
        $where[] = ['use_status', '=', 1];

        if ($servername) {
            $where[] = ['servername', 'like', "%$servername%"];
        }

        $lists = CrossServerModel::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'servername' => $servername,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="8">暂无数据</td>',
            'meta_title' => '服务器列表'
        ]);
        return $this->fetch();
    }

    /**
     * 创建跨服服务器
     */
    public function create()
    {
        /* 新增跨服 初始化 游戏数据库数据结构（cq_game?） */

        if (Request::isPost()) {
            $data = $_POST;
            $serverValidate = new ServerValidate();
            if (!$serverValidate->check($data)) {
                $this->error($serverValidate->getError());
            }

            /** @var TYPE_NAME $checkwhereid */
            $checkwhereid[] = ['id', '=', $data['id']];
            $checkId = CrossServerModel::where($checkwhereid)->find();
            if ($checkId) {
                $this->error('跨服服务器ID已存在！');
            }

            $checkwhere[] = ['servername', '=', trim($data['servername'])];
            $checkServerName = CrossServerModel::where($checkwhere)->find();
            if ($checkServerName) {
                $this->error('跨服服务器名称已存在！');
            }
            $data['db_database_name'] = $this->db_prefix . $data['id'];
            $data['real_server_id'] = $data['id'];
            $ret = CrossServerModel::insert($data);

            if ($ret) {
                action_log('cross_server_add', 'kuafu_server', $data['id'], UID);
                if ($this->create_database($data['db_database_name'])) {
                    $db = new DbManage($data['db_ip_w'], $data['db_username_w'], $data['db_password_w'], $data['db_database_name'], $this->charset);
                    $db->restore($this->sqlfile);
                }
                return $this->result($data, 1, '跨服服务器添加成功');
            } else {
                $this->error('快发服务器添加失败!');
            }
        } else {
            $this->assign([
                'meta_title' => '新增跨服服务器'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑跨服服务器
     * @param $id
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $info = Db::connect("db_config_main")->table('kuafu_server_list')->find($id);

        if (!$info) {
            $this->error('跨服服务器不存在或已删除！');
        }

        if (Request::isPost()) {
            $data = $_POST;
            $serverValidate = new ServerValidate();
            if (!$serverValidate->check($data)) {
                $this->error($serverValidate->getError());
            }
            $checkwhere[] = ['servername', '=', $data['servername']];
            $checkwhere[] = ['id', '<>', $data['id']];
            $checkServerName = Db::connect("db_config_main")
                ->table('kuafu_server_list')
                ->where($checkwhere)
                ->find();
            if ($checkServerName) {
                $this->error('跨服服务器名称已存在！');
            }

            $ret = Db::connect("db_config_main")
                ->table('kuafu_server_list')
                ->where('id', '=', $id)
                ->update($data);

            if ($ret) {
                action_log('cross_server_edit', 'kuafu_server', $id, UID);
                return json(['code' => 1, 'msg' => '跨服服务器信息修改成功!']);
            } else {
                $this->error('跨服服务器信息修改失败!');
            }
        } else {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑跨服服务器信息'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定资源
     */
    public function del()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        $data['use_status'] = 0;
        $res = CrossServerModel::where($where)->update($data);
        if ($res) {
            //添加行为记录
            action_log("cross_server_del", "kuafu_server", $ids, UID);
            $this->success('跨服删除成功!');
        } else {
            $this->error('跨服删除失败！');
        }
    }

    /**
     * 动态创建数据表
     * @param $dbname
     * @return bool
     */
    public function create_database($dbname)
    {
        $result = false;
        $con = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass);

        if (!$con) {
            Log::write('Could not connect: ' . mysqli_error($con));
            die('Could not connect: ' . mysqli_error($con));
        }
        if (mysqli_query($con, "CREATE DATABASE " . $dbname . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
            $result = true;
        } else {
            echo "Error creating database: " . mysqli_error($con);
        }
        mysqli_close($con);
        return $result;
    }
}
