<?php

namespace app\admin\controller;

use app\admin\model\ServerList;
use app\common\CrossServerManage;
use app\common\DbManage;
use app\common\ServerManage;
use app\common\test;
use think\Db;

use app\admin\validate\Server as ServerValidate;
use app\admin\validate\CombineServer as CombineServerValidate;

use think\facade\Log;
use think\facade\Request;
use think\facade\View;

define('DB_HOST', config('admin.DB_HOST'));
define('DB_USER', config('admin.DB_USER'));
define('DB_PASS', config('admin.DB_PASS'));
define('DB_PORT', config('admin.DB_PORT'));
define('DB_GAME_SQL_FILE', config('admin.DB_GAME_SQL_FILE'));

class Server extends Base
{
    private $dbhost = DB_HOST; //数据库主机名
    private $dbuser = DB_USER; //数据库用户名
    private $dbpass = DB_PASS; //数据库密码
    private $dbport = DB_PORT;//数据库端口
    private $database = null;
    private $charset = "utf8mb4";
    private $sqlfile = DB_GAME_SQL_FILE;
    protected $db_prefix = "fy_game";

    /**
     * 显示服务器资源列表
     */
    public function index()
    {
        $servername = trim(input('servername'));
        $kuafu_list = CrossServerManage::getCrossServerList();
        $where[] = ['use_status', '=', 1];

        if ($servername) {
            $where[] = ['servername', 'like', "%$servername%"];
        }

        $lists = ServerList::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'servername' => $servername,
            'lists' => $lists,
            'page' => $page,
            'kuafu_list' => $kuafu_list,
            'empty' => '<td class="empty" colspan="15">暂无数据</td>',
            'meta_title' => '服务器列表'
        ]);
        return $this->fetch();
    }

    /**
     * 显示已被合服服务器资源列表
     */
    public function combine_list()
    {
        $servername = trim(input('servername'));

        $where[] = ['use_status', '=', 0];

        if ($servername) {
            $where[] = ['servername', 'like', "%$servername%"];
        }

        $lists = ServerList::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'servername' => $servername,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '已被合服服务器列表'
        ]);
        return $this->fetch();
    }


    /**
     * 开服
     */
    public function create()
    {
        /* 开服 初始化 游戏数据库数据结构（cq_game?） */
        if (!request()->isPost()) 
        {
            $this->assign([ 'meta_title' => '新增服务器' ]);
            return $this->fetch();
        }
        //检查
        $data = $_POST;
        $serverValidate = new ServerValidate();
        if (!$serverValidate->check($data))
        {
            $this->error($serverValidate->getError());
        }
        
        /** @var TYPE_NAME $checkwhereid */
        $checkwhereid[] = ['id', '=', $data['id']];
        $checkId = ServerList::where($checkwhereid)->find();
        if ($checkId)
        {
            $this->error('服务器ID已存在！');
        }
        
        $checkwhere[] = ['servername', '=', trim($data['servername'])];
        $checkServerName = ServerList::where($checkwhere)->find();
        if ($checkServerName)
        {
            $this->error('服务器名称已存在！');
        }
        $data['open_time'] = strtotime($data['open_time']);
        $data['real_server_id'] = $data['id'];//开服真实服务器id即当前服务器id
        $data['db_database_name'] = $this->db_prefix . $data['id'];

        //创建游戏服务器数据库
        $error = '';
        if (!$this->create_database($data['db_database_name'],$error))
        {
            $this->error('创建游戏服务器数据库失败！'.$error);
        }
        $db = new DbManage($data['db_ip_w'], $data['db_username_w'], $data['db_password_w'], $data['db_database_name'], $this->charset);
        $db->restore($this->sqlfile);
        //增加数据
        $ret = ServerList::insert($data);
        if ($ret)
        {
            action_log('server_add', 'server_list', $data['id'], UID);
           // 判断是否创建游戏服务器数据库成功
            /** replace 全局标量数据表信息 **/
            if(!$this->replace_global_val($data['id']))
            {
                $this->error(' 导入游戏服务器数据库数据失败！');
            }
            //创建（新开）服务器发送命令
            test::refresh_server_info();
            return $this->result($data, 1, '服务器添加成功');
        }
        else
        {
            $this->error('服务器添加失败!');
        }
        return $this->fetch();
    }

    /**
     * 合服
     *（同一台物理机上数据库合并）
     */
    public function combine()
    {
        $server_list = ServerManage::getServerList();
        if (request()->isPost()) {
            $data = $_POST;

            /** @var TYPE_NAME $combineServerValidate */
            $combineServerValidate = new CombineServerValidate();
            if (!$combineServerValidate->check($data)) {
                $this->error($combineServerValidate->getError());
            }

            /**
             * 合服必须满足的条件
             * 合服与被合服的区服ID不能相等
             * 被合服的区服ID必须大于合服的区服ID
             */
            if (($data['server_id'] == $data['server_id_c']) || ($data['server_id'] > $data['server_id_c'])) {
                $this->error("您选择区服不符合合服规则!");
            }

            $svrlist = $this->getSvrList($data['server_id'], $data['server_id_c']);
            $db = new \app\common\DbManage($this->dbhost, $this->dbuser, $this->dbpass, $this->db_prefix . $data['server_id'], $this->dbport, $this->charset);
            $tables = $db->getTables();
            if (count($svrlist) > 0) {
                foreach ($svrlist as $svr) {
                    if ($svr['id'] != $data['server_id']) {
                        foreach ($tables as $table) {
                            if ($table == "player") {
                                $sql = $this->combineData($db, $table, "nickname", $data['server_id'], $svr['id']);
                            } elseif ($table == "sect") {
                                $sql = $this->combineData($db, $table, "sect_name", $data['server_id'], $svr['id']);
                            } elseif ($table == "global_val" || $table == "activity_data" || $table == "activity_first" ||
                                $table == "activity_lottory_limit_item" || $table == "activity_new_server_rank" || $table == "activity_time"
                                || $table == "guild_war_apply" || $table == "guild_war_record") {
                            } elseif ($table == "activity_super_recycle") {
                                //活动超级回收表特殊处理(取合服表与被合服表差集)TODO:
                                $this->diff_activity_super_recycle($data['server_id'], $svr['id']);
                            } elseif ($table == "activity_first") {
                                $this->compare_activity_first($data['server_id'], $svr['id']);
                            } else {
                                //TODO:update by sgy 2021-03-26 21:00
                                $sql = "insert IGNORE into" . " " . $this->db_prefix . $data['server_id'] . "." . $table . " " . "select * from" . " " . $this->db_prefix . $svr['id'] . "." . $table;
                            }
                            if (!empty($sql)) {
                                Log::write("合并数据表sql:" . $sql);
                                if ($db->_insert_into($sql)) {
                                    Log::write("合服sql语句执行失败!【sql:" . $sql . "】");
                                    $this->error('服务器添加失败!');
                                }
                            }
                        }
                        //备份数据库
                        $this->back_database($this->db_prefix . $svr['id']);

                        //合服成功修改被合服服务器状态
                        $this->updateServerStatus($svr['id'], $data['server_id']);

                        /**
                         * 合服记录信息
                         **/
                        $combineRecord['main_server'] = $data['server_id'];
                        $combineRecord['secondary_server'] = $svr['id'];
                        $combineRecord['combine_time'] = time();
                        $combineRecord['admin_id'] = UID;
                        $combineRecord['remark'] = "主服务器编号【" . $data['server_id'] . "】,被合服服务器编号【" . $svr['id'] . "】";
                        if (!\app\admin\model\CombineServerRecord::insert($combineRecord)) {
                            Log::write("服务器合并记录失败!!!!");
                        }
                    }
                }

                //修改合服时间/修改合服次数
                $this->updateServerCombineTime($data['server_id']);

                /* 合服成功重置vip_data表中的累计充值字段（total_value） */
                $this->updateTotalValue($data['server_id']);

                /* 合服成功删除buff表（player_buff）指定数据 */
                $this->del_buff($data['server_id']);

                /* 合服成功删除外观表（appearance）指定数据 */
                $this->del_appearance($data['server_id']);

                /* 合服成功删除外观穿戴（appearance_select）指定数据 */
                $this->del_appearance_select($data['server_id']);

                /* 合服成功，清空最近红包奖励领取记录 */
                $this->clear_red_packet_reward($data['server_id']);

                $this->clear_activity_data($data['server_id']);

                $this->clear_activity_time($data['server_id']);

                $this->clear_activity_new_server_rank($data['server_id']);

                $this->clear_guild_war_apply($data['server_id']);

                /* 合服成功 清空寻宝活动次数数据 */
                //$this->clear_activity_lottory_count($data['server_id']);

                /* 合服成功 清空寻宝活动奖励数据 */
                //$this->clear_activity_lottory_reward($data['server_id']);

                $this->clear_guild_war_record($data['server_id']);

                /* 合服成功，清空红包排行数据表记录 */
                $this->clear_red_packet_rank($data['server_id']);

                /* 合服成功，清空clear_activity_lottory_limit_item数据 */
                //$this->clear_activity_lottory_limit_item($data['server_id']);

                /* 合服成功，清空activity_use_vcion 数据 */
                //$this->clear_activity_use_vcion($data['server_id']);

                /* replace 全局变量表数据信息 */
                //$this->replace_global_val($data['server_id']);
                $this->special_replace_global_val($data['server_id']);

                //$this->clear_player_activity_recharge($data['server_id']);

                /* 合服成功，清空 player_activity_recharge_reward 数据表中 type 为 2,4,6*/
                //$this->del_player_activity_recharge_reward($data['server_id']);

                //$this->insert_appearance($data['server_id']);

                //创建（新开服）服务器发送命令
                test::refresh_server_info();
                return json(['code' => 1, 'msg' => '服务器合并成功!']);
//                return $this->result('',1,'服务器合并成功');
            } else {
                return $this->result($data, 1, '服务器列表为空,提交个鬼......');
            }
        } else {
            $this->assign([
                'serverlist' => $server_list,
                'server_id' => 0,
                'server_id_c' => 0,
                'meta_title' => '区服合并',
            ]);
            return $this->fetch();
        }
    }

    /**
     * 合服
     * 多台不同物理机数据库合并
     */
    public function combine_cross()
    {
        $server_list = ServerManage::getServerList();
        if (request()->isPost()) {
            $data = $_POST;

            /** @var TYPE_NAME $combineServerValidate */
            $combineServerValidate = new CombineServerValidate();
            if (!$combineServerValidate->check($data)) {
                $this->error($combineServerValidate->getError());
            }

            $server_id = $data['server_id'];
            $combine_ids = $data['server_id_c'];


            $svrlist = $this->getSvrListByIds($combine_ids);

            $db = new \app\common\DbManage($this->dbhost, $this->dbuser, $this->dbpass, $this->db_prefix . $data['server_id'], $this->dbport, $this->charset);
            $tables = $db->getTables();
            if (count($svrlist) > 0) {
                foreach ($svrlist as $svr) {
                    if ($svr['id'] != $data['server_id']) {
                        foreach ($tables as $table) {
                            if ($table == "player") {
                                $sql = $this->combineData($db, $table, "nickname", $data['server_id'], $svr['id']);
                            } elseif ($table == "sect") {
                                $sql = $this->combineData($db, $table, "sect_name", $data['server_id'], $svr['id']);
                            } elseif ($table == "global_val" || $table == "activity_data" || $table == "activity_first" ||
                                $table == "activity_lottory_limit_item" || $table == "activity_new_server_rank" || $table == "activity_time"
                                || $table == "guild_war_apply" || $table == "guild_war_record" || $table == "activity_server_name") {
                                //TODO:by sgy 2021-06-26 16:00
                                //global_val
                                //activity_lottory_limit_item 不做任何处理保留合服表的原数据

                            } elseif ($table == "activity_super_recycle") {
                                //活动超级回收表特殊处理(取合服表与被合服表差集)TODO:
                                $this->diff_activity_super_recycle($data['server_id'], $svr['id']);
                            } elseif ($table == "activity_first") {
                                $this->compare_activity_first($data['server_id'], $svr['id']);
                            } else {
                                $sql = "insert IGNORE into" . " " . $this->db_prefix . $data['server_id'] . "." . $table . " " . "select * from" . " " . $this->db_prefix . $svr['id'] . "." . $table;
                                Log::write("combine 排除满足条件的数据表执行的sql:" . $sql);
                            }
                            if (!empty($sql)) {
                                Log::write("combine cross 合并数据表sql:" . $sql);
                                if ($db->_insert_into($sql)) {
                                    Log::write("combine cross  合服sql语句执行失败!【sql:" . $sql . "】");
                                    $this->error('服务器添加失败!');
                                }
                            }
                        }
                        //备份数据库
                        $this->back_database("cq_game" . $svr['id']);

                        //合服成功修改被合服服务器状态
                        $this->updateServerStatus($svr['id'], $data['server_id']);

                        /**
                         * 合服记录信息
                         **/
                        $combineRecord['main_server'] = $data['server_id'];
                        $combineRecord['secondary_server'] = $svr['id'];
                        $combineRecord['combine_time'] = time();
                        $combineRecord['admin_id'] = UID;
                        $combineRecord['remark'] = "主服务器编号【" . $data['server_id'] . "】,被合服服务器编号【" . $svr['id'] . "】";
                        if (!\app\admin\model\CombineServerRecord::insert($combineRecord)) {
                            Log::write("combine cross  服务器合并记录失败!!!!");
                        }
                    }
                }

                //修改合服时间/修改合服次数
                $this->updateServerCombineTime($data['server_id']);

                /* 合服成功重置vip_data表中的累计充值字段（total_value） */
                $this->updateTotalValue($data['server_id']);

                /* 合服成功删除buff表（player_buff）指定数据 */
                $this->del_buff($data['server_id']);

                /* 合服成功删除外观表（appearance）指定数据 */
                $this->del_appearance($data['server_id']);

                /* 合服成功删除外观穿戴（appearance_select）指定数据 */
                $this->del_appearance_select($data['server_id']);

                /* 合服成功，清空最近红包奖励领取记录 */
                $this->clear_red_packet_reward($data['server_id']);

                //$this->clear_activity_data($data['server_id']);

                $this->clear_activity_time($data['server_id']);

                $this->clear_activity_new_server_rank($data['server_id']);

                $this->clear_guild_war_apply($data['server_id']);

                /* 合服成功 清空寻宝活动次数数据 */
                //TODO:by sgy 202--06-26 16:00
                //$this->clear_activity_lottory_count($data['server_id']);

                /* 合服成功 清空寻宝活动奖励数据 */
                //TODO:by sgy 202--06-26 16:00
                // $this->clear_activity_lottory_reward($data['server_id']);

                $this->clear_guild_war_record($data['server_id']);

                /* 合服成功，清空红包排行数据表记录 */
                $this->clear_red_packet_rank($data['server_id']);

                /* 合服成功，清空clear_activity_lottory_limit_item数据 */
                //$this->clear_activity_lottory_limit_item($data['server_id']);

                /* 合服成功，清空activity_use_vcion 数据 */
                //$this->clear_activity_use_vcion($data['server_id']);

                /* replace 全局变量表数据信息 */
                //$this->replace_global_val($data['server_id']);
                $this->special_replace_global_val($data['server_id']);


                //$this->clear_player_activity_recharge($data['server_id']);

                /* 合服成功，清空 player_activity_recharge_reward 数据表中 type 为 2,4,6*/
                //$this->del_player_activity_recharge_reward($data['server_id']);

                //$this->insert_appearance($data['server_id']);

                /** 合服成功,清空服务器冠名排行数据 */
                $this->clear_activity_server_name($data['server_id']);

                //创建（新开服）服务器发送命令
                test::refresh_server_info();
                $this->success('服务器合并成功!');
            } else {
                return $this->result($data, 1, '服务器列表为空,提交个鬼......');
            }
        } else {
            $this->assign([
                'server_list' => $server_list,
                'server_id' => 0,
                'meta_title' => '跨服合并数据库'
            ]);
            return $this->fetch();
        }
    }

    /**
     * 合并数据，校验数据是否重名,如重名在原column上加上下划线和服务器id
     * @param $db           数据库
     * @param $table_name   数据表
     * @param $column       数据字段
     * @param $server_id    合服服务器ID
     * @param $server_id_c  被合服服务器ID
     * @return string|null
     * @throws \think\Exception
     */
    public function combineData($db, $table_name, $column, $server_id, $server_id_c)
    {
        $arrDataSql = "select {$column} from " . " " . $this->db_prefix . $server_id . "." . $table_name;
        $arrData = $db->getColumns($arrDataSql);
        $checkArrSql = "select {$column} from " . " " . $this->db_prefix . $server_id_c . "." . $table_name;
        $checkArr = $db->getColumns($checkArrSql);
        $sql = null;

        //校验被合服的player数据表玩家名称是否重复,如重复在原nickname上加上下划线和服务器id
        foreach ($checkArr as $value) {
            $column_name = $value;
            if (in_array($value, $arrData)) {
                $value .= "_" . $server_id_c;
                $updateSql = "update" . " " . $this->db_prefix . $server_id_c . "." . $table_name . " " . "set {$column}=" . "'" . $value . "'" . " " . "where {$column}=" . "'" . $column_name . "'";

                if ($db->_insert_into($updateSql)) {
                    if ($table_name == "player") {
                        Log::write("更新被合区服玩家昵称" . $column_name . "失败!");
                    } else {
                        Log::write("更新被合区服公会名称" . $column_name . "失败!");
                    }
                } else {
                    /***/
                    $sql = "SELECT MAX(mail_id) as id from ";
                    $sql .= $this->db_prefix . $server_id . "." . "player_mail";
                    Log::write("获取玩家邮件表邮件最大ID sql:" . $sql);
                    $ret = $db->_query($sql);
                    $row = @mysqli_fetch_assoc($ret);
                    $mail_id = $row['id'] <> 0 ? $row['id'] : 0;
                    /***/

                    if ($table_name == "player") {
                        $prop_info = '20000085|1';
                        $player_info = dbConfig($server_id_c)->table('player')->where('nickname', '=', trim($value))->find();

                        $mail_info['mail_id'] = $mail_id + 1;
                        $mail_info['actor_id'] = $player_info['actor_id'];
                        $mail_info['mail_date'] = time();
                        $mail_info['title'] = "玩家同名处理邮件";
                        $mail_info['contents'] = "亲爱的玩家:" . $player_info['actor_id'] . "为了给玩家们提供更好的游戏体验并活跃游戏气氛，让玩家们结识更多的游戏伙伴、体验更多的挑战，《龙腾天下》顺应广大玩家的需求，将对部分区服进行合区处理。在合服过程中，出现跟您同名同姓志同道合的有缘人，限于游戏不能重名的规则，我们迫不得已给您的名字增加一些字符，为表歉意，特意为您送上补偿礼包一份。\r\n
                        重名规则：如果合服区有重名玩家，VIP等级高的玩家保持原名，VIP等级低的玩家在原名后面※1、※2依次下去；\r\n
                        赠送道具：改名卡1张，其他道具若干";

                        $mail_info['item_list'] = $prop_info;
                        if (!dbConfig($server_id)->table('player_mail')->insert($mail_info)) {
                            Log::write("玩家" . $player_info['actor_id'] . "邮件发送失败!");
                        }
                    } elseif ($table_name == "sect") {
                        //20000086-门派改名卡
                        $prop_info = '20000086|1';
                        $actor_id = dbConfig($server_id_c)
                            ->table('sect s')
                            ->join('sect_member sm', 's.sect_id=sm.sect_id')
                            ->field('sm.sect_id,sm.actor_id')
                            ->where('s.sect_name', '=', trim($value))
                            ->where('sm.title', '=', 100)   //title=100 掌门
                            ->value('sm.actor_id');

                        $mail_info['mail_id'] = $mail_id + 1;
                        $mail_info['actor_id'] = $actor_id;
                        $mail_info['mail_date'] = time();
                        $mail_info['title'] = "门派同名处理邮件";
                        $mail_info['contents'] = "亲爱的玩家:" . $actor_id .
                            "为了给玩家们提供更好的游戏体验并活跃游戏气氛，让玩家们结识更多的游戏伙伴、体验更多的挑战，《龙腾天下》顺应广大玩家的需求，将对部分区服进行合区处理。在合服过程中，出现跟您门派同名的情况，限于游戏不能重名的规则，我们迫不得已给您的门派名字增加一些字符，为表歉意，特意为您送上补偿礼包一份。\r\n
                        重名规则：如果公会同名，区服排前的保留名，后面的※1、※2依次下去;\r\n
                        赠送道具：行会改名卡1张，其他道具若干";
                        $mail_info['item_list'] = $prop_info;
                        if (!dbConfig($server_id)->table('player_mail')->insert($mail_info)) {
                            Log::write("掌门玩家" . $player_info['actor_id'] . "邮件发送失败!");
                        }
                    }
                }
            }
            $sql = "insert IGNORE into" . " " . $this->db_prefix . $server_id . "." . $table_name . " " . "select * from" . " " .
                $this->db_prefix . $server_id_c . "." . $table_name;
        }
        return $sql;
    }


    /**
     * 编辑服务器名称
     * @param $id
     * @return mixed
     * @throws \think\Exception
     */
    public function edit($id)
    {
        $info = Db::connect("db_config_main")->table('server_list')->find($id);

        if (!$info) 
        {
            $this->error('服务器不存在或已删除！');
        }

        if (!request()->isPost()) {
            $this->assign([
                'id' => $id,
                'info' => $info,
                'meta_title' => '编辑服务器信息'
            ]);
            return $this->fetch();
        } 
        //修改
        $data = $_POST;
        $serverValidate = new ServerValidate();
        if (!$serverValidate->check($data)) {
            $this->error($serverValidate->getError());
        }
        $checkwhere[] = ['servername', '=', $data['servername']];
        $checkwhere[] = ['id', '<>', $data['id']];
        $checkServerName = Db::connect("db_config_main")->table('server_list')->where($checkwhere)->find();
        
        if ($checkServerName)
        {
            $this->error('服务器名称已存在！');
        }
        
        $data['open_time'] = strtotime($data['open_time']);
       
        $ret = Db::connect("db_config_main")->table('server_list')->where('id', '=', $id)->update($data);
        
        if ($ret === false) 
        {
            $this->error('服务器信息修改失败!');
        }
        
        action_log('server_edit', 'server_list', $id, UID);
        //创建（新开服）服务器发送命令
        test::refresh_server_info();
        return json(['code' => 1, 'msg' => '服务器信息修改成功!']);
        //$this->success('服务器信息修改成功!', 'server/index');
    }


    /**
     * 动态创建数据表
     * @param $dbname
     * @return bool
     */
    public function create_database($dbname,&$error)
    {
        $result = false;
        $con = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass);

        if (!$con) 
        {
            Log::write('Could not connect: ' . mysqli_error($con));
            $error = 'Could not connect: ' . mysqli_error($con);
            //die('Could not connect: ' . mysqli_error($con));
        }
        if (mysqli_query($con, "CREATE DATABASE IF NOT EXISTS " . $dbname . " CHARACTER SET utf8mb4 COLLATE utf8mb4_bin")) 
        {
            $result = true;
        } 
        else 
        {
            Log::write('Error creating database: ' . mysqli_error($con));
            $error = 'Error creating database:  ' . mysqli_error($con);
            //die("Error creating database: " . mysqli_error($con));
        }
        mysqli_close($con);
        return $result;
    }

    /**
     * 删除数据库
     * @param $id
     * @return bool|\mysqli_result|void
     */
    public function drop_database($id)
    {
        if (isset($id)) {
            $dbname = $this->db_prefix . $id;
            $conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass);
            if (!$conn) {
                die('Could not connect: ' . mysqli_error($conn));
            }
            $ret = $this->check_database_is_exist($conn, $dbname);
            if ($ret) {
                $sql = "DROP DATABASE " . $dbname;
                $result = mysqli_query($conn, $sql);
                if (!$result) {
                    die('删除数据库失败: ' . mysqli_error($conn));
                }
                $this->success("数据库 " . $dbname . " 删除成功!");
                echo "数据库 " . $dbname . " 删除成功\n";
                mysqli_close($conn);
//                return $result;

            } else {
                $this->error('被合服数据库已清理,无需重复操作!');
            }
        }
    }

    /**
     * 判断数据库是否存在
     * @param $dbname
     * @return bool
     */
    public function check_database_is_exist($conn, $dbname)
    {
        $result = mysqli_query($conn, 'show databases;');
        $data = array();//用来存在数据库名
        mysqli_data_seek($result, 0);
        while ($dbdata = mysqli_fetch_array($result)) {
            $data[] = $dbdata['Database'];
        }
        mysqli_data_seek($result, 0);
        if (in_array($dbname, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /***
     * 服务器列表
     */
    public
    function getServerList()
    {
        $serverlist = ServerList::field('id,servername')
            ->select();
        $this->assign('serverlist', $serverlist);
    }

    /**
     * 根据已选择的服务器ID筛选被合服的服务器ID或范围
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function getServerListBySelectId($id)
    {
        $where[] = [
            ['id', '>', $id],
            ['use_status', '=', 1]
        ];
        return ServerList::where($where)->select();
    }

    /**
     * 根据条件获取服务器列表
     * @param $svr_start_id
     * @param $svr_end_id
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function getSvrList($svr_start_id, $svr_end_id)
    {
        $where[] = [
            ['use_status', '=', 1],
            ['id', 'between', [$svr_start_id, $svr_end_id]]
        ];
        return ServerList::where($where)
            ->field('id,servername')
            ->select();
    }

    /**
     * 根据服务器IDs组查询服务器列表
     * @param $server_ids
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function getSvrListByIds($server_ids)
    {
        Log::write("get svr list by ids:");
        Log::write(ServerList::where([['use_status', '=', 1], ['id', 'in', $server_ids]])
            ->field('id,servername')->fetchSql(true)
            ->select());
        return ServerList::where([['use_status', '=', 1], ['id', 'in', $server_ids]])
            ->field('id,servername')
            ->select();
    }


    /**
     * 关闭服务器
     * 发送命令到服务器
     * @param $id
     * @throws \think\Exception
     */
    public
    function server_shut_down($id)
    {
        if (empty($id)) 
        {
            $this->error('请选择关停的服务器!');
        }
        $info = Db::connect("db_config_main")->table('server_list')->find($id);
        if (!$info) {
            $this->error('服务器不存在或已删除！');
        }
        test::webw_packet_shut_down_server($id);
        $this->result($id, 1, '关停服务器请求发送成功,待服务器处理......');
    }

    /**
     * 服务器是否自动开服、自动开服条件变更
     */
    public
    function auto_open_config()
    {
        if (Request::isPost()) {
            $value = 0;
            if (isset($_POST['value']) && !empty($_POST['value'])) {
                $value = $_POST['value'];
            }

            $condition = 0;
            if (isset($_POST['condition']) && !empty($_POST['condition'])) {
                $condition = $_POST['condition'];
            }
            Db::connect('db_config_main')->table('server_config')->where('id', 1)->update(['value' => $value]);
            Db::connect('db_config_main')->table('server_config')->where('id', 2)->update(['value' => $condition]);
            test::change_server_config();
            $this->success('自动开服配置成功！');
        } else {
            $is_open = Db::connect('db_config_main')->table('server_config')->where('id', '=', 1)->value('value');
            $auto_condition = Db::connect('db_config_main')->table('server_config')->where('id', '=', 2)->value('value');
            View::assign([
                'is_open' => $is_open ? $is_open : 0,
                'auto_condition' => $auto_condition ? $auto_condition : 0
            ]);
            return View::fetch();
        }
    }

    /**
     * 是否实名开关设置
     */
    public
    function authentication()
    {
        if (Request::isPost()) {
            $value = 0;
            if (isset($_POST['value']) && !empty($_POST['value'])) {
                $value = $_POST['value'];
            }
            Db::connect('db_config_main')->table('server_config')->where('id', 3)->update(['value' => $value]);
            test::change_server_config();
            $this->success('是否实名设置成功！');
        } else {
            $is_open = 0;
            $info = Db::connect('db_config_main')->table('server_config')->where('id', '=', 3)->find();
            if ($info) {
                $is_open = $info['value'];
            } else {
                $data['id'] = 3;
                $data['value'] = 0;
                Db::connect('db_config_main')->table('server_config')->insert($data);
            }
            View::assign([
                'is_open' => $is_open ? $is_open : 0,
            ]);
            return View::fetch();
        }
    }


    /**
     * 一键修改服务器维护状态
     */
    public
    function batch_edit_server()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $map1 = [['id', 'in', $ids]];
        $map2 = [['real_server_id', 'in', $ids]];
        $data['status'] = 3;

        $ret = ServerList::whereOr([$map1, $map2])->update($data);
        if ($ret) {
            action_log('server_status_edit', 'server', $ret, UID);
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 1,
                'msg' => '服务器维护状态修改成功!'
            ];
            test::refresh_server_info();
            return json($resData);
        } else {
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 0,
                'msg' => '服务器维护状态修改失败!'
            ];
            return json($resData);
        }
    }

    /**
     * 一键修改服务器爆满状态
     **/
    public
    function batch_edit_server_full()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $map1 = [['id', 'in', $ids]];
        $map2 = [['real_server_id', 'in', $ids]];
        //1爆满,2新服,3维护
        $data['status'] = 1;

        $ret = ServerList::whereOr([$map1, $map2])->update($data);
        if ($ret) {
            action_log('server_status_edit', 'server', $ret, UID);
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 1,
                'msg' => '服务器爆满状态修改成功!'
            ];
            test::refresh_server_info();
            return json($resData);
        } else {
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 0,
                'msg' => '服务器爆满状态修改失败!'
            ];
            return json($resData);
        }
    }


    /**
     * 一键修改服务器新服状态
     **/
    public
    function batch_edit_server_new()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $map1 = [['id', 'in', $ids]];
        $map2 = [['real_server_id', 'in', $ids]];
        //1爆满,2新服,3维护
        $data['status'] = 2;

        $ret = ServerList::whereOr([$map1, $map2])->update($data);
        if ($ret) {
            action_log('server_status_edit', 'server', $ret, UID);
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 1,
                'msg' => '服务器新服状态修改成功!'
            ];
            test::refresh_server_info();
            return json($resData);
        } else {
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 0,
                'msg' => '服务器新服状态修改失败!'
            ];
            return json($resData);
        }
    }


    /**
     * 编辑服务器状态
     */
    public
    function change_status()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $map1 = [['id', '=', $data['id']]];
            $map2 = [['real_server_id', '=', $data['id']]];

            $ret = ServerList::whereOr([$map1, $map2])->update(['status' => $data['status']]);

            if ($ret !== false) {
                action_log('server_status_edit', 'server', $ret, UID);
                $resData = [
                    'data' => $data,
                    'code' => 0,
                    'msg' => '服务器状态修改成功!'
                ];
                test::refresh_server_info();
                return json($resData);
            } else {
                $resData = [
                    'data' => $data,
                    'code' => -1,
                    'msg' => '服务器状态修改失败!'
                ];
                return json($resData);
            }
        }
    }

    /**
     * 设置服务器自动开服开关
     * @param $id
     * @throws \think\Exception
     */
    public
    function set_switch($id = 1)
    {
        if (Request::isPost()) {
            $data = $_POST;
            $info = Db::connect('db_config_main')->table('server_config')->find($id);
            if ($info) {
                $info_data['id'] = 1;
                $info_data['value'] = $data['val'];
                if (Db::connect('db_config_main')->table('server_config')->update($info_data)) {
                    //自动开服服务器变更信息发送命令
                    test::change_server_config();
                    $this->success('操作成功！', 'auto_open_config');
                } else {
                    $this->error('操作失败');
                }
            } else {
                if ($data['is_open'] == 1) {
                    $info_data['id'] = 1;
                    $info_data['value'] = $data['val'];
                    if (Db::connect('db_config_main')->table('server_config')->insert($info_data)) {
                        //自动开服服务器变更信息发送命令
                        test::change_server_config();
                        $this->success('操作成功！', 'auto_open_config');
                    } else {
                        $this->error('操作失败');
                    }
                }
            }
        }
    }

    /**
     * 开服人数条件变更
     * @param int $id
     * @throws \think\Exception
     */
    public
    function change_condition($id = 2)
    {
        if (Request::isPost()) {
            $data = $_POST;
            $info = Db::connect('db_config_main')->table('server_config')->find($id);
            if ($info) {
                $info_data['id'] = 2;
                $info_data['value'] = $data['auto_condition'];
                if (Db::connect('db_config_main')->table('server_config')->update($info_data)) {
                    //自动开服服务器变更信息发送命令
                    test::change_server_config();
                    $this->success('操作成功！', 'auto_open_config');
                } else {
                    $this->error('操作失败');
                }
            } else {
                $info_data['id'] = 2;
                $info_data['value'] = $data['auto_condition'];
                if (Db::connect('db_config_main')->table('server_config')->insert($info_data)) {
                    //自动开服服务器变更信息发送命令
                    test::change_server_config();
                    $this->success('操作成功！', 'auto_open_config');
                } else {
                    $this->error('操作失败');
                }
            }
        }
    }

    /**
     * 设置跨服ID
     */
    public
    function set_cross_server()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $server_id = $data['id'];
            $cross_id = $data['cross_id'];
            $where[] = [['id', '=', $server_id]];

            $ret = ServerList::where($where)->update(['kuafu_id' => $cross_id]);

            if ($ret) {
                action_log('set_cross_server', 'server', $ret, UID);
                $resData = [
                    'data' => $data,
                    'code' => 0,
                    'msg' => '服务器跨服设置成功!'
                ];
                test::webw_packet_assign_kuafu($cross_id, $server_id);
                return json($resData);
            } else {
                $resData = [
                    'data' => $data,
                    'code' => -1,
                    'msg' => '服务器跨服设置失败!'
                ];
                return json($resData);
            }
        }
    }

    /**
     * 取消跨服
     * 发送命令到服务器
     * @param $id   服务器ID
     * @throws \think\Exception
     */
    public
    function cancel_cross_server($id)
    {
        if (empty($id)) {
            $this->error('请选择取消跨服的服务器!');
        }
        $info = Db::connect("db_config_main")
            ->table('server_list')
            ->find($id);
        if (!$info) {
            $this->error('服务器不存在或已删除！');
        }
        $ret = ServerList::where('id', $id)->update(['kuafu_id' => 0]);
        if ($ret) {
            action_log('cancel_cross_server', 'server', $ret, UID);
            test::webw_packet_assign_kuafu(0, $id);
            $this->result($id, 1, '取消服务器ID【' . $id . '】跨服请求发送成功,待服务器处理......');
        } else {
            $this->result($id, 0, '取消跨服设置失败,请重新操作!');
        }
    }

    /**
     * 一键取消跨服
     */
    public
    function batch_cancel_cross_server()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }

        $where[] = [['id', 'in', $ids]];
        $data['kuafu_id'] = 0;

        $ret = ServerList::where($where)->update($data);
        if ($ret) {
            action_log('batch_cancel_cross_server', 'server', $ret, UID);
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 1,
                'msg' => '批量取消跨服成功!'
            ];
            test::webw_packet_assign_kuafu(0, $ids);
            return json($resData);
        } else {
            $resData = [
                'data' => '',
                'ids' => $ids,
                'code' => 0,
                'msg' => '批量取消跨服失败!'
            ];
            return json($resData);
        }
    }

    /**
     * 批量设置跨服
     */
    public
    function batch_set_cross_server()
    {
        $server_id = trim(input('server_id'));
        $kuafu_id = trim(input('kuafu_id'));
        $server_list = ServerManage::getServerList();
        $kuafu_server_list = CrossServerManage::getCrossServerList();
        if (Request::isPost()) {
            $data = $_POST;
            $server_ids = $data['server_id'];
            $cross_id = $data['kuafu_id'];
            $ret = ServerList::where([['id', 'in', $data['server_id']]])->update(['kuafu_id' => $cross_id]);
            if ($ret) {
                action_log('batch_set_cross_server', 'server', $ret, UID);
                test::webw_packet_assign_kuafu($cross_id, $server_ids);
                $this->result($ret, 1, '批量设置服务器ID【' . $server_ids . '】跨服请求发送成功,待服务器处理......');
            } else {
                $this->result($ret, 0, '批量跨服设置失败,请重新操作!');
            }
        } else {
            View::assign([
                'server_id' => $server_id,
                'kuafu_id' => $kuafu_id,
                'server_list' => $server_list,
                'cross_server_list' => $kuafu_server_list,
                'meta_title' => '跨服设置'
            ]);
            return View::fetch();
        }
    }


    /**
     * 修改服务器合并时间
     * @param $server_id
     */
    public
    function updateServerCombineTime($server_id)
    {
        $ser = Db::connect("db_config_main")
            ->table('server_list')
            ->where('id', $server_id)
            ->inc('combine_count')
            ->update([
                'combine_time' => time(),
                'combine_count' => Db::raw('combine_count+1')
            ]);

        if (!$ser) {
            return false;
        }
        return true;
    }

    /**
     * 修改游戏服务器状态
     * @param $server_id 被合服的ID
     * @param $combine_server_id 合服ID
     * @throws \think\Exception
     */
    public
    function updateServerStatus($server_id, $combine_server_id)
    {
        $serverData['use_status'] = 0;
        $serverData['real_server_id'] = $combine_server_id;
        $serverData['combine_time'] = time();

        $map1 = [['id', '=', $server_id]];
        $map2 = [['real_server_id', '=', $server_id]];

        // update sgy 2021-06-04 11:20
        // where('id', '=', $server_id)
        $ser = Db::connect("db_config_main")
            ->table('server_list')
            ->whereOr([$map1, $map2])    // update sgy 2021-06-04 11:20
            ->update($serverData);

        if (!$ser) {
            Log::write("服务器状态修改失败!");
            return false;
        }
        return true;
    }

    /**
     * 修改服务器名称
     */
    public
    function updateServerName()
    {
        if (!request()->isPost())
        {
            $this->error('非法请求！');
        } 
        $data['id'] = input('id');
        $data['servername'] = input('servername');
        
        $res = ServerList::update($data);
        if (!$res)
        {
            $this->error('服务器名称修改失败！');
        }
        //添加行为记录
        action_log("server_edit_name", "server", $data['id'], UID);
        //发送命令到服务器请求刷新服务器列表信息
        test::refresh_server_info();
        $this->success('服务器名称修改成功！');
    }


    /**
     * 修改被合服服务器vip_data数据表 累计充值金额
     * @param $server_id    服务器ID
     * @throws \think\Exception
     */
    public
    function updateTotalValue($server_id)
    {
        $data['total_value'] = 0;
        $ret = dbConfig($server_id)->table('vip_data')->where('total_value', '>', 0)->update($data);
        if (!$ret) {
            Log::write('玩家累计充值金额重置失败或暂无数据!');
            return false;
        }
        return true;
    }


    /**
     * 合服删除指定player_buff数据
     * @param $server_id
     */
    public
    function del_buff($server_id)
    {
        //delete from player_buff where id = 33901 or id = 33902 or id = 33903 or id = 33904
        $items = $this->get_red_envelopes_rank();
        $ids = '';
        for ($i = 0; $i < count($items); $i++) {
            if ($items[$i]['buff_id'] != 0) {
                $ids .= $items[$i]['buff_id'] . ',';
            }
        }
        if (empty($ids)) {
            Log::write('ids参数为空');
            return false;
        }
        $ids = substr($ids, 0, strlen($ids) - 1);
        Log::write(dbConfig($server_id)->table('player_buff')->where('id', 'in', $ids)->fetchSql(true)->delete());
        $ret = dbConfig($server_id)->table('player_buff')->where('id', 'in', $ids)->delete();
        if (!$ret) {
            Log::write('合服删除指定buff数据失败或暂无buff数据!');
            return false;
        }
        return true;
    }


    /**
     * 合服删除指定appearance数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function del_appearance($server_id)
    {
        $ids = [1001, 2001, 3001, 4001, 5001, 5, 12, 13, 24, 25, 26];

        $ret = dbConfig($server_id)->table('appearance')->where('appearance_id', 'in', $ids)->delete();
        if (!$ret) {
            Log::write('合服删除指定外观数据失败或暂无外观数据!');

            return false;
        }

        if (!$this->insert_appearance($server_id)) {
            Log::write("appearance插入指定称号数据失败!!!");
        }
        return true;
    }

    /**
     * 合服删除指定外观穿戴数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function del_appearance_select($server_id)
    {
        $map = [
            [
                ['appearance_type', '=', 4],
                ['subtype', 'in', [9, 21, 22, 23]]
            ],
            [
                ['appearance_type', '=', 1],
                ['subtype', '=', 1]
            ],
            [
                ['appearance_type', '=', 2],
                ['subtype', '=', 2]
            ],
            [
                ['appearance_type', '=', 3],
                ['subtype', '=', 100]
            ],
            [
                ['appearance_type', '=', 4],
                ['subtype', '=', 26]
            ],
            [
                ['appearance_type', '=', 5],
                ['subtype', '=', 4]
            ],
            [
                ['appearance_type', '=', 5],
                ['subtype', '=', 5]
            ],
            [
                ['appearance_type', '=', 13],
                ['subtype', '=', 5]
            ]
        ];


        $ret = dbConfig($server_id)->table('appearance_select')->whereOr($map)->delete();
        if (!$ret) {
            Log::write('合服删除指定外观穿戴数据失败或暂无穿戴外观数据!');
            return false;
        }
        return true;
    }

    /**
     * 清空最近红包奖励记录信息
     * @param $server_id
     */
    public
    function clear_red_packet_reward($server_id)
    {
        $ret = dbConfig($server_id)->table('red_packet_last_day_reward')->delete(true);
        if (!$ret) {
            Log::write('清空近期红包奖励记录信息失败或暂无红包奖励记录!');
            return false;
        }
        return true;
    }

    public
    function clear_activity_lottory_limit_item($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_lottory_limit_item')->delete(true);
        if (!$ret) {
            return false;
        }
        return true;
    }

    /**
     * 清空活动使用元宝
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_use_vcion($server_id)
    {
        //activity_use_vcion
        $ret = dbConfig($server_id)->table('activity_use_vcion')->delete(true);
        if (!$ret) {
            return false;
        }
        return true;
    }

    /**
     * 清空红包排行数据表
     * @param $server_id
     * @throws \think\Exception
     */
    public
    function clear_red_packet_rank($server_id)
    {
        $ret = dbConfig($server_id)->table('red_packet_rank')->delete(true);
        if (!$ret) {
            Log::write('清空红包排行数据表失败或暂无红包排行数据!');
            return false;
        }
        return true;
    }

    /**
     * 清空活动数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_data($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_data')->delete(true);
        if (!$ret) {
            Log::write('清空活动数据表失败或暂无活动数据!');
            return false;
        }
        return true;
    }

    /**
     * 清空活动时间
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_time($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_time')->delete(true);
        if (!$ret) {
            Log::write('清空活动时间数据表失败或暂无活动时间数据!');
            return false;
        }
        return true;
    }

    /**
     * 清空合服后的活动排行数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_new_server_rank($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_new_server_rank')->delete(true);
        if (!$ret) {
            Log::write('清空活动新服排行数据表失败或暂无活动新服排行数据!');
            return false;
        }
        return true;
    }

    /**
     * 合服活动超级回收数据表取差值
     * @param $server_id        合服ID
     * @param $server_id_c      被合服ID
     * @return bool
     * @throws \think\Exception
     */
    public
    function diff_activity_super_recycle($server_id, $server_id_c)
    {
        $recycle_arr = dbConfig($server_id)->table('activity_super_recycle')->order('id asc')->select();
        $compare_recycle_arr = dbConfig($server_id_c)->table('activity_super_recycle')->order('id asc')->select();
        //判断合服表超级回收表是否有数据，如无数据则不作任何处理
        $recycle_count = count($recycle_arr);
        if ($recycle_count == 0) {
            return;
        }

        $compare_recycle_arr_count = count($compare_recycle_arr);
        if ($compare_recycle_arr_count == 0) {
            dbConfig($server_id)->table('activity_super_recycle')->delete(true);
            Log::write("被合服的超级回收数据表无数据,清空合服超级回收数据表数据！");
            return;
        }

        $ret_arr = [];
        for ($i = 0; $i < $recycle_count; $i++) {
            for ($j = 0; $j < $compare_recycle_arr_count; $j++) {
                if ($recycle_arr[$i]['id'] == $compare_recycle_arr[$j]['id']) {
                    $temp['id'] = $recycle_arr[$i]['id'];
                    if ($recycle_arr[$i]['get_count'] > $compare_recycle_arr[$j]['get_count']) {
                        $temp['get_count'] = $compare_recycle_arr[$j]['get_count'];
                    } else {
                        $temp['get_count'] = $recycle_arr[$i]['get_count'];
                    }
                    array_push($ret_arr, $temp);
                }
            }
        }
        if (dbConfig($server_id)->table('activity_super_recycle')->delete(true)) {
            if (dbConfig($server_id)->table('activity_super_recycle')->insertAll($ret_arr)) {
                Log::write("活动超级回收数据添加成功!!!!");
            } else {
                Log::write("活动超级回收数据添加失败!!!!");
            }
        } else {
            Log::write($this->db_prefix . $server_id . "库中的数据表【activity_super_recycle】失败!");
            return false;
        }
        return true;
    }

    /**
     * @param $server_id
     * @param $server_id_c
     * @return bool
     * @throws \think\Exception
     */
    public
    function compare_activity_first($server_id, $server_id_c)
    {
        $first_1 = dbConfig($server_id)->table('activity_first')->select();
        $first_2 = dbConfig($server_id_c)->table('activity_first')->select();

        $temp_arr = array();
        if (count($first_1) > 0 && count($first_2) > 0) {
            foreach ($first_1 as $value) {
                foreach ($first_2 as $value2) {
                    if (($value['first_type'] == $value2['first_type']) && ($value['first_id'] == $value2['first_id'])) {
                        if ($value['insert_time'] <= $value2['insert_time']) {
                            array_push($temp_arr, $value);
                        } else {
                            array_push($temp_arr, $value2);
                        }
                    }
                }
            }

            if (count($temp_arr) > 0) {
                //首先清空原表数据
                dbConfig($server_id)->table('activity_first')->delete(true);
                if (dbConfig($server_id)->table('activity_first')->insertAll($temp_arr)) {
                    Log::write("合服合并Activity_First数据表数据成功!");
                    return true;
                } else {
                    Log::write("合服合并Activity_First数据表数据失败!");
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * 清空武都争霸请求数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_guild_war_apply($server_id)
    {
        $ret = dbConfig($server_id)->table('guild_war_apply')->delete(true);
        if (!$ret) {
            Log::write('清空武都争霸申请数据表失败或暂无武都争霸申请数据!');
            return false;
        }
        return true;
    }

    /**
     * 寻宝活动次数数据表清理
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_lottory_count($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_lottory_count')->delete(true);
        if (!$ret) {
            Log::write('寻宝活动次数数据表失败或暂无寻宝活动次数数据!');
            return false;
        }
        return true;
    }

    /**
     * 寻宝活动奖励数据表清理
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_activity_lottory_reward($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_lottory_reward')->delete(true);
        if (!$ret) {
            Log::write('寻宝活动奖励数据表失败或暂无寻宝活动奖励数据!');
            return false;
        }
        return true;
    }

    /**
     * 清空清空武都争霸记录数据
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_guild_war_record($server_id)
    {
        $ret = dbConfig($server_id)->table('guild_war_record')->delete(true);
        if (!$ret) {
            Log::write('清空武都争霸记录数据表失败或暂无武都争霸记录数据!');
            return false;
        }
        return true;
    }

    /**
     * replace 全局标量数据表信息
     * @param $server_id
     * @throws \think\Exception
     */
    public
    function replace_global_val($server_id)
    {
        $db = dbConfig($server_id);
        if(!$db)
        {
            return false;
        }
        $table_name = 'global_val';
        $data['id'] = 3;
        $data['val'] = 20000;
        $res = $db->table($table_name)->insert($data);
        if (!$res)
        {
            Log::write('全局变量表【' . $this->db_prefix . $server_id . $table_name . ' 】数据插入失败!');
            return false;
        }
        return true;
    }

    /**
     * 全局标量数据表信息(特殊处理)
     * @param $server_id
     * @throws \think\Exception
     */
    public
    function special_replace_global_val($server_id)
    {
        $table_name = 'global_val';
        $ret = dbConfig($server_id)->table($table_name)->where('id', '<>', 6)->delete();
        if (!$ret) {
            Log::write("特殊处理全局变量数据表global_val 数据清空失败!");
        }
        $data['id'] = 3;
        $data['val'] = 20000;
        if (!dbConfig($server_id)->table($table_name)->insert($data)) {
            Log::write('特殊处理全局变量表' . $this->db_prefix . $server_id . $table_name . ' 数据插入失败!');
        }
    }

    /**
     * 清空玩家活动充值指定字段信息
     * @param $server_id
     * @return bool
     * @throws \think\Exception
     */
    public
    function clear_player_activity_recharge($server_id)
    {
        $table_name = 'player_activity_recharge';
        $ret = dbConfig($server_id)->table($table_name)->where('1=1')->update(
            [
                'last_recharge_time' => 0,
                'last_recharge100_time' => 0,
                'last_recharge500_time' => 0
            ]
        );
        if (!$ret) {
            Log::write('玩家活动充值数据清空失败或暂无玩家活动充值数据!');
            return false;
        }
        return true;
    }


    /**
     * 玩家活动充值奖励
     * @param $server_id
     */
    public
    function del_player_activity_recharge_reward($server_id)
    {
        $table_name = 'player_activity_recharge_reward';
        $ret = dbConfig($server_id)->table($table_name)->where([['type', 'in', [2, 4, 6]]])->delete(true);
        if (!$ret) {
            Log::write('玩家活动充值奖励数据清空失败或暂无玩家活动充值奖励数据!');
            return false;
        }
        return true;
    }

    /**
     * 备份数据库
     * @param $dbname
     */
    public
    function back_database($dbname)
    {
        $db = new \app\common\DbManage($this->dbhost, $this->dbuser, $this->dbpass, $dbname, $this->dbport, $this->charset);
        $back_database = $db->backup("", "", 200000);
        Log::write("back data base " . $dbname . "备份完成！！！");
    }

    /**
     * 红包排行奖励csv列表
     */
    public
    function get_red_envelopes_rank()
    {
        $file_name = '../public/csv/RedenvelopesRank.csv';
        $file_open = fopen($file_name, 'r');
        $count = 1;
        $items = array();
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count > 1) {
                for ($i = 0; $i < count($data); $i++) {
                    $item['title_id'] = mb_convert_encoding($data[2], "UTF-8", "GBK");//称号ID
                    $item['buff_id'] = mb_convert_encoding($data[3], 'UTF-8', 'GBK');//buff_id
                }
                array_push($items, $item);
            }
            $count++;
        }
        fclose($file_open);
        return $items;
    }

    /**
     * 清空服务器冠名排行数据表
     * @param $server_id
     * @throws \think\Exception
     */
    public
    function clear_activity_server_name($server_id)
    {
        $ret = dbConfig($server_id)->table('activity_server_name')->delete(true);
        if (!$ret) {
            Log::write('清空服务器冠名排行数据表失败或暂冠名排行数据!');
            return false;
        }
        return true;
    }

    /**
     * 获取外观（读表）
     **/
    public
    function get_appearance()
    {
        $file_name = '../public/csv/Appearance.csv';
        $file_open = fopen($file_name, 'r');
        $count = 1;
        $items = array();
        $item = array();
        while (!feof($file_open) && $data = fgetcsv($file_open)) {
            if (!empty($data) && $count > 1) {
                for ($i = 0; $i < 4; $i++) {
                    if ($data[0] == 24) {
                        $item['id'] = $data[0];
                        $item['type'] = $data[2];
                        $item['child_type'] = $data[3];
                        array_push($items, $item);
                    }
                }
            }
            $count++;
        }
        fclose($file_open);
        var_dump($items);
        return $items;
    }

    /**
     * 合服成功插入外观
     * @param $server_id
     * @return false
     */
    public
    function insert_appearance($server_id)
    {
        /**
         * insert into appearance(actor_id,appearance_id,limit_time) (select actor_id,13,0 from top_list order by fighting_point desc limit 1);
         *  insert into appearance(actor_id,appearance_id,limit_time) (select actor_id,5,0 from top_list where job = 100 order by fighting_point desc limit 1);
         *  insert into appearance(actor_id,appearance_id,limit_time) (select actor_id,5,0 from top_list where job = 101 order by fighting_point desc limit 1);
         *  insert into appearance(actor_id,appearance_id,limit_time) (select actor_id,5,0 from top_list where job = 102 order by fighting_point desc limit 1);
         **/

        $actor_id = dbConfig($server_id)->table('top_list')->order('fighting_point desc')->limit(1)->value('actor_id');
        $actor_id_job_100 = dbConfig($server_id)->table('top_list')->where('job', 100)->order('fighting_point desc')->limit(1)->value('actor_id');
        $actor_id_job_101 = dbConfig($server_id)->table('top_list')->where('job', 101)->order('fighting_point desc')->limit(1)->value('actor_id');
        $actor_id_job_102 = dbConfig($server_id)->table('top_list')->where('job', 102)->order('fighting_point desc')->limit(1)->value('actor_id');
        $appearance = [
            ['actor_id' => $actor_id, 'appearance_id' => 13, 'limit_time' => 0],
            ['actor_id' => $actor_id_job_100, 'appearance_id' => 5, 'limit_time' => 0],
            ['actor_id' => $actor_id_job_101, 'appearance_id' => 5, 'limit_time' => 0],
            ['actor_id' => $actor_id_job_102, 'appearance_id' => 5, 'limit_time' => 0]
        ];

        $ret = dbConfig($server_id)->table('appearance')->insertAll($appearance);
        if ($ret) {
            return true;
        } else {
            Log::write("insert appearance failed！！！");
            return false;
        }
    }
}
