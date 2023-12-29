<?php

namespace app\admin\controller;


use app\admin\model\ServerList;
use app\admin\model\UserInfo as UserInfoModel;
use app\common\ServerManage;
use page\Page;
use think\Db;
use think\facade\Log;
use think\facade\View;

define('GROUP_ID', config('admin.GROUP_ID'));

/**
 * 聊天相关操作类
 **/
class ChatLog extends Base
{
    public $table_prefix = "cq_game";

    public function index()
    {
        $is_guild = false;
        $ids = '';
        $temp_server_ids = '';
        if (GROUPID == GROUP_ID) {
            $is_guild = true;
            $s_ids = get_user_server_list(UID);
            $temp_server_ids = '';
            foreach ($s_ids as $key => $value) {
                $temp_server_ids .= $value['server_id'] . ',';
            }
            $ids = explode(',', rtrim($temp_server_ids, ","));
            $server_list = ServerManage::getServerListByIds($ids);
        } else {
            $server_list = ServerManage::getServerList();
        }
        $start_server_id = trim(input('start_server_id'));
        $end_server_id = trim(input('end_server_id'));
        $start_date = trim(input('start_date'));
        $end_date = trim(input('end_date'));
        $server_id = trim(input('server_id'));
        $player_name = trim(input('player_name'));
        $chat_content = trim(input('chat_content'));
        $type = trim(input('chat_type'));

        $filed = 'logid,serverId,serverName,actor_id,from_name,to_name,chat_type,chat_content,logtime';
        $table_prefix = 'chat_log';//表前缀
        $table_name = $table_prefix . date("Ymd"); //初始默认查询当日数据表
        $where_str = ' 1=1';

        $search = false;
        if ((!empty($start_server_id) && $start_server_id > 0)
            && (!empty($end_server_id) && $end_server_id > 0)
            && $end_server_id > $start_server_id) {
            $where_str .= ' and serverId between ' . $start_server_id . ' and ' . $end_server_id . '';
            $search = true;
        }

        //多服选择
//        $server_ids = '';
//        if ($server_id && $server_id != -1) {
//            $server_ids = explode(',', $server_id);
//        }

        //search==false 排除选择了服务器区间条件
        if ($search == false && $server_id) {
            $where_str .= " and serverId =" . $server_id;
        }

        if (empty($server_id) && $is_guild == true) {
            $s_id = get_user_server_by_id(UID);
            $resInfo = ServerManage::getServerInfoByGuild($s_id);
            if ($resInfo) {
                $server_id = $resInfo['id'];
            }
            $where_str .= " and serverId =" . $server_id;
        }

        //消息发送或接收者角色昵称
        if ($player_name) {
            $where_str .= " and (from_name like " . '"%' . $player_name . '%"' . " or to_name like " . '"%' . $player_name . '%"' . ")";
        }

        //聊天内容模糊查询
        if ($chat_content) {
            $where_str .= " and chat_content like " . '"%' . $chat_content . '%"';
        }

        //消息类型查询
        if ($type) {
            //特殊判断type==0
            if ($type == 100) {
                $where_str .= " and chat_type=0";
            } elseif ($type == -1) {
            } else {
                $where_str .= " and chat_type=" . $type;
            }
        }

        $curr_page = input('page/d', 1);
        $lists = '';
        $lists_sql = '';
        $total = 0;//数据记录总数
        if (isset($start_date) && !empty($start_date)) {
            //开始日期不为空
            if (isset($end_date) && !empty($end_date)) {
                if ($start_date == $end_date) {
                    $table_name = $table_prefix . date('Ymd', strtotime($start_date));
                    if ($this->check_exists_table($table_name)) {
                        $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' ';
                    }
                } else if ($start_date < $end_date) {
                    //TODO:开始日期小于等于结束日期
                    $table_name = $table_prefix . date('Ymd', strtotime($start_date));

                    if ($this->check_exists_table($table_name)) {
                        $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' ';
                        $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
                        if ($diff > 0) {
                            for ($i = 1; $i <= $diff; $i++) {
                                $union_table = $table_prefix . date("Ymd", strtotime("+$i day", strtotime($start_date)));
                                //容错，判断消息数据表是否存在 TODO：
                                if ($this->check_exists_table($union_table)) {
                                    $lists_sql .= 'union all select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . ' ';
                                }
                            }
                        }
                    } else {
                        // TODO: 基础表不存在
                        $base = false;
                        $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
                        if ($diff > 0) {
                            for ($i = 1; $i <= $diff; $i++) {
                                $union_table = $table_prefix . date("Ymd", strtotime("+$i day", strtotime($start_date)));
                                //容错，判断消息数据表是否存在 TODO：
                                if ($this->check_exists_table($union_table)) {
                                    //确定一张数据表作为基础表
                                    if ($base == false) {
                                        $lists_sql = 'select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . ' ';
                                        $base = true;
                                    } else {
                                        $lists_sql .= 'union all select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . ' ';
                                    }
                                } else {
                                    Log::write("不存在的数据表:" . $union_table . PHP_EOL);
                                }
                            }
                        }
                        Log::write("执行sql：" . $lists_sql);
                    }
                } else {
                    $table_name = $table_prefix . date('Ymd', strtotime($start_date));
                    if ($this->check_exists_table($table_name)) {
                        $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' ';
                    }
                }
            } else {
                $table_name = $table_prefix . date('Ymd', strtotime($start_date));
                if ($this->check_exists_table($table_name)) {
                    $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . ' ';
                } else {
                    $lists_sql = '';
                }
            }
        } else {
            $exists_table = Db::connect('db_chat_log')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
            if ($exists_table) {
                $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . '  ';
            } else {
                $lists_sql = '';
            }
        }

        Log::write("chat log lists sql:" . $lists_sql);
        if (!empty($lists_sql)) {
            $total = count(Db::connect('db_chat_log')->query($lists_sql));//统计数据总数
            $lists_sql .= ' order by logtime desc limit ?,?';
            $lists = Db::connect('db_chat_log')->query($lists_sql, [($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS')]);
            $pagernator = Page::make($lists, config('LIST_ROWS'), $curr_page, $total, false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
            $page = $pagernator->render();
        } else {
            $total = 0;
            $lists = '';
            $page = '';
        }

        View::assign([
            'server_list' => $server_list,
            'start_server_id' => $start_server_id,
            'end_server_id' => $end_server_id,
            'server_id' => $server_id, //$server_ids,
            'start_date' => $start_date ? $start_date : date('Y-m-d'),
            'end_date' => $end_date ? $end_date : date('Y-m-d'),
            'player_name' => $player_name,
            'chat_content' => $chat_content,
            'chat_type' => $type,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="7">暂无数据</td>',
            'meta_title' => '历史消息记录列表'
        ]);
        return View::fetch();
    }

    /**
     * 检测数据表是否存在
     * @param $table_name
     * @return
     * @throws \think\Exception
     */
    public function check_exists_table($table_name)
    {
        return Db::connect('db_chat_log')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
    }
}
