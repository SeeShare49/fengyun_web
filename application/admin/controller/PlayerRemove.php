<?php

namespace app\admin\controller;

use app\admin\model\Users as UserModel;
use app\common\ServerManage;
use app\admin\model\PlayerRemove as PlayerModel;
use think\Db;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

/**
 * 用户角色清理操作记录信息
 * 清理条件：七天未登陆，且无充值，且等级低于80级
 */
class PlayerRemove extends Base
{
    /**
     * 操作记录数据列表
     **/
    public function index()
    {
        $server_id = 5;
        $days = 7;//默认7天
        $before_7d = strtotime(date('Y-m-d H:i:s', strtotime("-" . $days . " day")));

        $where1[] = [
            ['level', '<', 80],
            ['last_login_time', '<', $before_7d],
            ['last_logout_time', '<', $before_7d]
        ];

        $start_date = trim(input('start_date'));
        $end_date = trim(input('end_date'));
        $where[] = ["1", '=', 1];
        if ($start_date && $end_date) {
            $start_date = $start_date;
            $end_date = $end_date;
            $where[] = ['create_time', 'between', [strtotime($start_date . " 00:00:00"), strtotime($end_date . " 23:59:59")]];
        }

        $server_list = ServerManage::getServerList();
        $server_id = trim(input('server_id'));
        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }
        $lists = PlayerModel::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);

        $this->ifPageNoData($lists);
        $page = $lists->render();
        View::assign([
            'server_id' => $server_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'server_list' => $server_list,
            'lists' => $lists,
            'page' => $page,
            'empty' => '<td class="empty" colspan="6">暂无数据</td>',
            'meta_title' => '用户角色清理操作记录信息'
        ]);
        return View::fetch();
    }


    /**
     * 用户角色清理操作
     * 清理条件：七天未登陆，且无充值，且等级低于80级
     */
    public function clear()
    {
        if (Request::isPost()) {
            $data = $_POST;
            $server_id = $data['server_id'];
            if(!isset($server_id)||empty($server_id)){
                $this->error("清空所有数据,胆子有点肥?请选择服务器！！！");
            }
            $days = $data['days'];//默认7天
            $before_7d = strtotime(date('Y-m-d H:i:s', strtotime("-" . $days . " day")));

            $where[] = [
                ['level', '<', 80],
                ['last_login_time', '<', $before_7d],
                ['last_logout_time', '<', $before_7d]
            ];

            $count = dbConfigByReadBase($server_id)->table('player')->where($where)->count('actor_id');
            if ($count > 0) {
                //执行条件
                $str_where = " `level`<80 and `last_login_time`<" . $before_7d . " and `last_logout_time`<" . $before_7d . "";
                $clear_table = 0;
                //activity_boss_score
                $query_str = "delete from activity_boss_score where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                Log::write("activity_boss_score query str:" . $query_str);
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_first
                $query_str = "delete from activity_first where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_hero_power
                $query_str = "delete from activity_hero_power where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_lottory_count
                $query_str = "delete from activity_lottory_count where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_lottory_rank
                $query_str = "delete from activity_lottory_rank where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_lottory_reward
                $query_str = "delete from activity_lottory_reward where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_new_server_rank
                $query_str = "delete from activity_new_server_rank where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_sign_in
                $query_str = "delete from activity_sign_in where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_use_vcion
                $query_str = "delete from activity_use_vcion where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //activity_use_wing
                $query_str = "delete from activity_use_wing where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //all_time_limit
                $query_str = "delete from all_time_limit where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //appearance
                $query_str = "delete from appearance where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //buy_investment_plan
                $query_str = "delete from buy_investment_plan where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //client_setting
                $query_str = "delete from client_setting where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //consignment
                $query_str = "delete from consignment where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //consignment_price
                $query_str = "delete from consignment_price where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //consignment_vcoin
                $query_str = "delete from consignment_vcoin where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //endless_tower
                $query_str = "delete from endless_tower where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //equip_slot_upgrade
                $query_str = "delete from equip_slot_upgrade where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //every_day_limit
                $query_str = "delete from every_day_limit where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //furnace
                $query_str = "delete from furnace where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //hero
                $query_str = "delete from hero where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //investment_plan_goal
                $query_str = "delete from investment_plan_goal where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //invite_code
                $query_str = "delete from invite_code where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //legond_equip
                $query_str = "delete from legond_equip where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //lottery_record
                $query_str = "delete from lottery_record where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //luck_out_item
                $query_str = "delete from luck_out_item where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //month_card
                $query_str = "delete from month_card where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_gem_gift
                $query_str = "delete from player_activity_gem_gift where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_gem_gift_reward
                $query_str = "delete from player_activity_gem_gift_reward where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge
                $query_str = "delete from player_activity_recharge where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_double
                $query_str = "delete from player_activity_recharge_double where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_first
                $query_str = "delete from player_activity_recharge_first where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_luckpanel
                $query_str = "delete from player_activity_recharge_luckpanel where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_reward
                $query_str = "delete from player_activity_recharge_reward where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_shop
                $query_str = "delete from player_activity_recharge_shop where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_activity_recharge_turnpanel
                $query_str = "delete from player_activity_recharge_turnpanel where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_bin_data
                $query_str = "delete from player_bin_data where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_boss_personal
                $query_str = "delete from player_boss_personal where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_buff
                $query_str = "delete from player_buff where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_gift
                $query_str = "delete from player_gift where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_gift_code_get
                $query_str = "delete from player_gift_code_get where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_item
                $query_str = "delete from player_item where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_mail
                $query_str = "delete from player_mail where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);

                //player_shortcut
                $query_str = "delete from player_shortcut where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                Log::write("player shortcut query str:" . $query_str);
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_skill
                $query_str = "delete from player_skill where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //player_status
                $query_str = "delete from player_status where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //quest_completed
                $query_str = "delete from quest_completed where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //quest_data
                $query_str = "delete from quest_data where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //quest_goal_data
                $query_str = "delete from quest_goal_data where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //quest_rand
                $query_str = "delete from quest_rand where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //quest_repeat
                $query_str = "delete from quest_repeat where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //red_packet_last_day_reward
                $query_str = "delete from red_packet_last_day_reward where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //red_packet_rank
                $query_str = "delete from red_packet_rank where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //relationship
                $query_str = "delete from relationship where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //sect_member
                $query_str = "delete from sect_member where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //sect_redpacket_info
                $query_str = "delete from sect_redpacket_info where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //sect_skill
                $query_str = "delete from sect_skill where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //star_river
                $query_str = "delete from star_river where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //top_list
                $query_str = "delete from top_list where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //treasure
                $query_str = "delete from treasure where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //vip_boss
                $query_str = "delete from vip_boss where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //vip_boss_state
                $query_str = "delete from vip_boss_state where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //vip_data
                $query_str = "delete from vip_data where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

                //weekly_limit
                $query_str = "delete from weekly_limit where actor_id = ANY (select actor_id from player where " . $str_where . ")";
                dbConfig($server_id)->query($query_str);
                $clear_table++;

//                dbConfig($server_id)->table('player')->where($where)->delete();
                Log::write("待删除的player数据表:" . dbConfig($server_id)->table('player')->where($where)->fetchSql(true)->select());
                $clear_table++;
                $record['server_id'] = $server_id;
                $record['server_name'] = get_server_name($server_id);
                $record['role_number'] = $count;
                $record['table_number'] = $clear_table;
                $record['operator'] = USERNAME;
                $record['create_time'] = time();
                if (PlayerModel::insert($record)) {
                    $this->success("用户角色清理记录成功!", "player_remove/index");
                } else {
                    $this->error("用户角色清理记录失败!");
                }
            } else {
                $this->success("暂未匹配符合条件的用户角色数据!", "player_remove/index");
            }

        } else {
            $server_list = ServerManage::getServerList();
            View::assign([
                'server_list' => $server_list,
                'meta_title' => '用户角色清理'
            ]);
            return View::fetch();
        }
    }
}
