<?php

namespace app\admin\controller;

use app\admin\model\UserChannel;
use app\common\GameLogActionManage;
use app\common\GameLogActionType;
use app\common\ServerManage;
use page\Page;
use think\Db;
use think\facade\Log;


define('GROUP_ID', config('admin.GROUP_ID'));
/** 混服管理组 **/
define('MIX_GROUP_ID', config('admin.MIX_GROUP_ID'));

/**
 * 商城消耗
 */
class ShopMall extends Base
{
    public function index()
    {
        /** 商城module_id=4 **/
        $where_str = ' moduleId=4 and actionId=2 ';
        if (GROUPID == MIX_GROUP_ID) {
            $channel_ids = UserChannel::where('uid', '=', UID)->value('channel_ids');
            if (empty($channel_ids)) {
                $this->error('该管理员用户未配置渠道,请联系管理员!');
            }
            $where_str .= " and channel_id in  (" . $channel_ids . ")";
        }

        $ids = '';
        $is_guild = false;
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


        $start_date = trim(input('start_date'));
        $end_date = trim(input('end_date'));

        /**
            remark =3,4,102,30000
            第一位标识道具数量，
            第二位标识商店类型，
            第三位标识货币类型（元宝、金币、银票），
            第四位标识货币数量
         **/

        $filed = "logid,serverId,serverName,userId,playerName,moduleId,moduleName,value,actionName,remark,logtime,
        SUM(SUBSTRING_INDEX( remark, ',', 1 ))  AS propNum,SUBSTRING_INDEX(SUBSTRING_INDEX(remark,',',2),',',-1) as shopType,
        SUBSTRING_INDEX(SUBSTRING_INDEX(remark,',',3),',',-1) as currencyType,SUM(SUBSTRING_INDEX( remark, ',',- 1 )) AS currencyNum,
        case when LENGTH(0+remark) = LENGTH(remark) then 1 else 0 end as is_numerical";
        $table_name = "Log" . date("Ymd"); //初始默认查询当日数据表
        $exists_table = Db::connect('db_config_log')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        //$exists_table = Db::connect('db_config_log_read')->query('SHOW TABLES LIKE ' . "'" . $table_name . "'");
        if (!$exists_table) {
            $this->error("数据表【{$table_name}】不存在！！！！");
        }


        $server_id = trim(input('server_id'));
        $server_ids = '';
        if (isset($server_id) && !empty($server_id)) {
            $server_ids = explode(',', $server_id);
            $where_str .= " and serverId in (" . $server_id . ")";
        }


        /** 商店类型 **/
        $shop_type_id = trim(input('shop_type_id'));
        if ($shop_type_id) {
            $where_str .= ' and SUBSTRING_INDEX(SUBSTRING_INDEX(remark,",",2),",",-1)=' . $shop_type_id;
        }

        //道具ID
        $prop_value = trim(input('prop_value'));
        if ($prop_value) {
            $where_str .= ' and value=' . $prop_value;
        }

        $user_id = trim(input('userId'));
        if ($user_id) {
            $where_str .= ' and userId=' . $user_id;
        }

        //追踪ID
        $guid = trim(input('guid'));
        if ($guid) {
            $where_str .= ' and guid=' . $guid;
        }

        $player_name = trim(input('playerName'));
        if ($player_name) {
            $where_str .= " and playerName like '%" . $player_name . "%'";
        }
        $lists = '';
        $lists_sql = '';
        $total = 0;
        $curr_page = input('page/d', 1);
        if (isset($start_date) && !empty($start_date)) {
            //开始日期不为空
            if (isset($end_date) && !empty($end_date)) {
                if ($start_date == $end_date) {
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                } else if ($start_date < $end_date) {
                    // TODO:开始日期小于等于结束日期
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                    $diff = intval((strtotime($end_date) - strtotime($start_date)) / 86400);
                    if ($diff > 0) {
                        for ($i = 1; $i <= $diff; $i++) {
                            $union_table = 'Log' . date("Ymd", strtotime("+$i day", strtotime($start_date)));
                            $lists_sql .= 'union all select ' . $filed . ' from ' . $union_table . ' where ' . $where_str . ' ';
                        }
                    } else {
                        $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                    }
                } else {
                    $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
                }
            } else {
                $lists_sql = 'select ' . $filed . ' from ' . " Log" . date('Ymd', strtotime($start_date)) . ' where ' . $where_str . ' ';
            }
        } else {
            $lists_sql = 'select ' . $filed . ' from ' . $table_name . ' where ' . $where_str . '  ';
        }

        $lists_sql .= "group by shopType,currencyType ";
        $total = Db::connect('db_config_log')->execute($lists_sql);//统计数据总数

        //$total = count(Db::connect('db_config_log_read')->query($lists_sql));//统计数据总数
        $lists_sql .= ' order by logtime desc limit ?,?';

        // $lists = Db::connect('db_config_log_read')->query($lists_sql, [($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS')]);
        $lists = Db::connect('db_config_log')->query($lists_sql, [($curr_page - 1) * config('LIST_ROWS'), config('LIST_ROWS')]);
        $pagernator = Page::make($lists, config('LIST_ROWS'), $curr_page, $total, false, ['path' => Page::getCurrentPath(), 'query' => request()->param()]);
        $page = $pagernator->render();


        $this->assign([
            'server_id' => $server_ids,
            'prop_value' => $prop_value,
            'shop_type_id' => $shop_type_id,
            'playerName' => $player_name,
            'start_date' => isset($start_date) ? $start_date : date('Y-m-d'),
            'end_date' => isset($end_date) ? $end_date : date('Y-m-d'),
            'user_id' => $user_id,
            'guid' => $guid,
            'server_list' => $server_list,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="10">暂无数据</td>',
            'meta_title' => '道具流向列表'
        ]);
        return $this->fetch();
    }
}
