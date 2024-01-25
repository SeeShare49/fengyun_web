/*
 Navicat MySQL Data Transfer

 Source Server         : 121.40.166.20
 Source Server Type    : MySQL
 Source Server Version : 80032
 Source Host           : 121.40.166.20:3306
 Source Schema         : fy_game1

 Target Server Type    : MySQL
 Target Server Version : 80032
 File Encoding         : 65001

 Date: 19/01/2024 15:25:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for aaa
-- ----------------------------
DROP TABLE IF EXISTS `aaa`;
CREATE TABLE `aaa`  (
  `a1` int NOT NULL DEFAULT 0,
  `b1` int NULL DEFAULT 0,
  `b2` int NULL DEFAULT 0,
  PRIMARY KEY (`a1`) USING BTREE,
  INDEX `index_b1_b2`(`b1`, `b2`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for artifact
-- ----------------------------
DROP TABLE IF EXISTS `artifact`;
CREATE TABLE `artifact`  (
  `actor_id` bigint NOT NULL,
  `table_id` int NOT NULL DEFAULT 0 COMMENT '神器表id',
  `number` int NULL DEFAULT 0 COMMENT '拥有个数',
  PRIMARY KEY (`actor_id`, `table_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for artifact_forge
-- ----------------------------
DROP TABLE IF EXISTS `artifact_forge`;
CREATE TABLE `artifact_forge`  (
  `actor_id` bigint NOT NULL,
  `slot_index` int NOT NULL,
  `forge_id` int NULL DEFAULT 0 COMMENT '锻造表id',
  `start_time` int NULL DEFAULT 0 COMMENT '开始时间',
  `speed_up_second` int NULL DEFAULT 0 COMMENT '加速时间',
  `speed_up_num` int NULL DEFAULT 0 COMMENT '加速次数',
  PRIMARY KEY (`actor_id`, `slot_index`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for artifact_tactical
-- ----------------------------
DROP TABLE IF EXISTS `artifact_tactical`;
CREATE TABLE `artifact_tactical`  (
  `actor_id` bigint NOT NULL,
  `front_id` int NOT NULL DEFAULT 0 COMMENT '阵法id',
  `slot` int NOT NULL DEFAULT 0 COMMENT '五行栏位',
  `artifact_table_id` int NULL DEFAULT 0 COMMENT '神器表id',
  PRIMARY KEY (`actor_id`, `front_id`, `slot`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card`  (
  `actor_id` bigint NOT NULL,
  `card_table_id` int NOT NULL,
  `count` int NULL DEFAULT NULL,
  PRIMARY KEY (`actor_id`, `card_table_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card_activation
-- ----------------------------
DROP TABLE IF EXISTS `card_activation`;
CREATE TABLE `card_activation`  (
  `actor_id` bigint NOT NULL,
  `card_table_id` int NOT NULL,
  PRIMARY KEY (`actor_id`, `card_table_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card_group_activation
-- ----------------------------
DROP TABLE IF EXISTS `card_group_activation`;
CREATE TABLE `card_group_activation`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `group_id` int NOT NULL DEFAULT 0,
  `card_table_id` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `group_id`, `card_table_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for client_other_setting
-- ----------------------------
DROP TABLE IF EXISTS `client_other_setting`;
CREATE TABLE `client_other_setting`  (
  `actor_id` bigint NOT NULL,
  `set_key` int NOT NULL,
  `val` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `set_key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for client_setting
-- ----------------------------
DROP TABLE IF EXISTS `client_setting`;
CREATE TABLE `client_setting`  (
  `actor_id` bigint NOT NULL,
  `set_key` int NOT NULL,
  `val` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `set_key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for consignment
-- ----------------------------
DROP TABLE IF EXISTS `consignment`;
CREATE TABLE `consignment`  (
  `ident_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `price_start` int NULL DEFAULT 0,
  `price_now` int NULL DEFAULT 0,
  `price_buyout` int NULL DEFAULT 0,
  `start_time` int NULL DEFAULT 0,
  `end_time` int NULL DEFAULT 0,
  `type_id` int NULL DEFAULT 0,
  `item_flags` int NULL DEFAULT 0,
  `number` int NULL DEFAULT 0,
  `create_time` int NULL DEFAULT 0,
  `sell_price_type` int NULL DEFAULT 0,
  `sell_price` int NULL DEFAULT 0,
  PRIMARY KEY (`ident_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6969122618637589361 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for consignment_price
-- ----------------------------
DROP TABLE IF EXISTS `consignment_price`;
CREATE TABLE `consignment_price`  (
  `ident_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `price_auction` int NULL DEFAULT 0,
  PRIMARY KEY (`ident_id`, `actor_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 927598496780128257 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for consignment_record
-- ----------------------------
DROP TABLE IF EXISTS `consignment_record`;
CREATE TABLE `consignment_record`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `seller` bigint NULL DEFAULT 0 COMMENT '卖方',
  `buyer` bigint NULL DEFAULT 0 COMMENT '买方',
  `deal_price` int NULL DEFAULT 0 COMMENT '成交价',
  `tax` int NULL DEFAULT 0 COMMENT '手续费',
  `item_guid` bigint NULL DEFAULT 0 COMMENT '物品ID',
  `time` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for consignment_vcoin
-- ----------------------------
DROP TABLE IF EXISTS `consignment_vcoin`;
CREATE TABLE `consignment_vcoin`  (
  `actor_id` bigint NOT NULL,
  `vcoin` int NOT NULL,
  PRIMARY KEY (`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for demon_hunt_quest
-- ----------------------------
DROP TABLE IF EXISTS `demon_hunt_quest`;
CREATE TABLE `demon_hunt_quest`  (
  `actor_id` bigint NOT NULL,
  `quest_index` int NOT NULL,
  `quest_id` int NULL DEFAULT 0 COMMENT '任务表id',
  PRIMARY KEY (`actor_id`, `quest_index`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for equip_reset
-- ----------------------------
DROP TABLE IF EXISTS `equip_reset`;
CREATE TABLE `equip_reset`  (
  `reset_index` int NOT NULL,
  `is_temp` int NOT NULL DEFAULT 0 COMMENT '临时数据标识',
  `data_type` int NULL DEFAULT 0 COMMENT '属性类型',
  `data_value` int NULL DEFAULT 0 COMMENT '属性值',
  `has_lock` int NULL DEFAULT 0 COMMENT '锁定状态',
  `table_id` int NULL DEFAULT 0 COMMENT 'EquipReset表id',
  `item_ident_id` bigint NOT NULL DEFAULT 0 COMMENT '物品唯一id',
  PRIMARY KEY (`reset_index`, `is_temp`, `item_ident_id`) USING BTREE,
  INDEX `index_item_ident_id`(`item_ident_id`) USING BTREE,
  CONSTRAINT `foreign_item_ident_id` FOREIGN KEY (`item_ident_id`) REFERENCES `player_item` (`ident_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for field_boss
-- ----------------------------
DROP TABLE IF EXISTS `field_boss`;
CREATE TABLE `field_boss`  (
  `field_boss_id` int NOT NULL,
  `refresh_place_index` int NULL DEFAULT 0,
  `hp` int NULL DEFAULT 100,
  `last_kill_tick` bigint NULL DEFAULT 0,
  PRIMARY KEY (`field_boss_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for game_limit
-- ----------------------------
DROP TABLE IF EXISTS `game_limit`;
CREATE TABLE `game_limit`  (
  `actor_id` bigint NOT NULL,
  `limit_type` int NOT NULL DEFAULT 0 COMMENT '限制类型',
  `limit_enum` int NOT NULL DEFAULT 0 COMMENT '限制的枚举id',
  `limit_id` int NOT NULL,
  `refurbish_hour` int NOT NULL DEFAULT 0 COMMENT '刷新时间',
  `limit_value` int NULL DEFAULT 0,
  `last_refurbish_time` bigint NULL DEFAULT 0 COMMENT '上一次刷新时间',
  PRIMARY KEY (`actor_id`, `limit_type`, `limit_id`, `refurbish_hour`, `limit_enum`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for game_money
-- ----------------------------
DROP TABLE IF EXISTS `game_money`;
CREATE TABLE `game_money`  (
  `actor_id` bigint NOT NULL,
  `money_type` int NOT NULL,
  `money_value` bigint NULL DEFAULT NULL,
  PRIMARY KEY (`actor_id`, `money_type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for global_val
-- ----------------------------
DROP TABLE IF EXISTS `global_val`;
CREATE TABLE `global_val`  (
  `id` int NOT NULL,
  `val` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investment_plan_goal
-- ----------------------------
DROP TABLE IF EXISTS `investment_plan_goal`;
CREATE TABLE `investment_plan_goal`  (
  `actor_id` bigint NOT NULL,
  `goal_id` int NOT NULL,
  `get_reward` int NULL DEFAULT NULL,
  `get_count` int NULL DEFAULT NULL,
  PRIMARY KEY (`actor_id`, `goal_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for invite_code
-- ----------------------------
DROP TABLE IF EXISTS `invite_code`;
CREATE TABLE `invite_code`  (
  `actor_id` bigint NOT NULL,
  `invite_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  PRIMARY KEY (`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for lottery_count
-- ----------------------------
DROP TABLE IF EXISTS `lottery_count`;
CREATE TABLE `lottery_count`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `lottery_type` int NOT NULL DEFAULT 0 COMMENT '抽奖类型',
  `lottery_count` int NULL DEFAULT 0 COMMENT '抽奖次数',
  PRIMARY KEY (`actor_id`, `lottery_type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for lottery_free_time
-- ----------------------------
DROP TABLE IF EXISTS `lottery_free_time`;
CREATE TABLE `lottery_free_time`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `lottery_type` int NOT NULL DEFAULT 0 COMMENT '抽奖类型',
  `free_time` int NULL DEFAULT 0 COMMENT '上一次免费抽的时间',
  PRIMARY KEY (`actor_id`, `lottery_type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for player
-- ----------------------------
DROP TABLE IF EXISTS `player`;
CREATE TABLE `player`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `account_id` bigint NULL DEFAULT 0,
  `nickname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '昵称',
  `level` int NULL DEFAULT 1 COMMENT '等级',
  `exp` bigint NULL DEFAULT 0,
  `job` int NULL DEFAULT 0 COMMENT '职业',
  `official` int NULL DEFAULT 0,
  `gender` int NULL DEFAULT 0 COMMENT '性别',
  `online` int NULL DEFAULT 0,
  `deleted` int NULL DEFAULT 0 COMMENT '是否已删除',
  `create_time` bigint NULL DEFAULT 0,
  `pk_value` int NULL DEFAULT 0,
  `talk_prohibited` int NULL DEFAULT 0,
  `depot_slot_add` int NULL DEFAULT 0,
  `bag_slot_add` int NULL DEFAULT 0,
  `cur_power` int NULL DEFAULT 0,
  `last_vcoin_used_by_day` int NULL DEFAULT 0,
  `vcoin_used_by_day` int NULL DEFAULT NULL,
  `vcoin_used_by_month` int NULL DEFAULT 0,
  `last_congzhi_month` int NULL DEFAULT 0,
  `vcoin_accu_by_month` int NULL DEFAULT 0,
  `last_login_time` bigint NULL DEFAULT 0,
  `last_login_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `last_logout_time` bigint NULL DEFAULT NULL,
  `exit_map` bigint NULL DEFAULT NULL,
  `seed_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `online_time_all` bigint NULL DEFAULT 0,
  `online_time_today` bigint NULL DEFAULT 0,
  `online_tag_today` bigint NULL DEFAULT 0,
  `online_time_yestoday` bigint NULL DEFAULT 0,
  `online_tag_yestoday` bigint NULL DEFAULT 0,
  `offline_time` bigint NULL DEFAULT 0,
  `offline_tag` bigint NULL DEFAULT 0,
  `achieve_point` int NULL DEFAULT 0,
  `achieve_game_money_max` int NULL DEFAULT 0,
  `online_time_max` bigint NULL DEFAULT 0,
  `login_day_continue` int NULL DEFAULT 0,
  `login_day_continue_max` int NULL DEFAULT 0,
  `login_day_count` int NULL DEFAULT 0,
  `cur_hp` bigint NULL DEFAULT 0,
  `cur_mp` bigint NULL DEFAULT 0,
  `exit_x` int NULL DEFAULT 0,
  `exit_y` int NULL DEFAULT 0,
  `safe_map` bigint NULL DEFAULT NULL,
  `safe_map_x` int NULL DEFAULT 0,
  `safe_map_y` int NULL DEFAULT 0,
  `lottory_point` int NULL DEFAULT 0,
  `boss_score` int NULL DEFAULT 0,
  `using_tactical` int NULL DEFAULT 0 COMMENT '法阵',
  `sign_in` int NULL DEFAULT 0 COMMENT '签到天数',
  `invite_code_count` int NULL DEFAULT 0,
  `attack_mode` int NULL DEFAULT 101,
  `create_server_id` int NULL DEFAULT 0,
  `forbid_chat` int NULL DEFAULT 0,
  `channel_id` int NOT NULL DEFAULT 1 COMMENT '渠道ID',
  `rune_essence` int NULL DEFAULT 0 COMMENT '符文精华',
  `hair` int NULL DEFAULT 0,
  `color` int NULL DEFAULT 0,
  `country` int NULL DEFAULT 0,
  `change_country_time` bigint NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`) USING BTREE,
  UNIQUE INDEX `index_nickname`(`nickname`) USING BTREE,
  INDEX `index_account`(`account_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_buff
-- ----------------------------
DROP TABLE IF EXISTS `player_buff`;
CREATE TABLE `player_buff`  (
  `actor_id` bigint NOT NULL,
  `id` int NOT NULL DEFAULT 0,
  `end_val` double(11, 3) NULL DEFAULT 0.000,
  `use_type` int NULL DEFAULT 0,
  `dynamic_prop` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  PRIMARY KEY (`actor_id`, `id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_dungeon
-- ----------------------------
DROP TABLE IF EXISTS `player_dungeon`;
CREATE TABLE `player_dungeon`  (
  `actor_id` bigint NOT NULL,
  `type` int NOT NULL,
  `free_times` int NULL DEFAULT 0,
  `pay_times` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_dungeon_ext
-- ----------------------------
DROP TABLE IF EXISTS `player_dungeon_ext`;
CREATE TABLE `player_dungeon_ext`  (
  `actor_id` bigint NOT NULL,
  `daily_fresh_tick` bigint NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_equip_slot
-- ----------------------------
DROP TABLE IF EXISTS `player_equip_slot`;
CREATE TABLE `player_equip_slot`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `bag_index` int NOT NULL DEFAULT 1,
  `position` int NOT NULL DEFAULT 0,
  `lv` int NULL DEFAULT 0,
  `star` int NULL DEFAULT 0,
  `unlock_holes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  PRIMARY KEY (`actor_id`, `bag_index`, `position`) USING BTREE,
  INDEX `actor_bag_index`(`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_gift
-- ----------------------------
DROP TABLE IF EXISTS `player_gift`;
CREATE TABLE `player_gift`  (
  `id` int NOT NULL DEFAULT 0,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `used` int NULL DEFAULT 0,
  `bind` int NULL DEFAULT 0,
  `num` int NULL DEFAULT 0,
  `type_id` int NULL DEFAULT 0,
  `msg` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_gift_code_get
-- ----------------------------
DROP TABLE IF EXISTS `player_gift_code_get`;
CREATE TABLE `player_gift_code_get`  (
  `actor_id` bigint NOT NULL,
  `gift_type` int NOT NULL,
  PRIMARY KEY (`actor_id`, `gift_type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_item
-- ----------------------------
DROP TABLE IF EXISTS `player_item`;
CREATE TABLE `player_item`  (
  `ident_id` bigint NOT NULL,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `bag_index` int NULL DEFAULT 0 COMMENT '背包索引',
  `position` int NULL DEFAULT 0,
  `type_id` int NULL DEFAULT 0,
  `duration` int NULL DEFAULT 0,
  `number` int NULL DEFAULT 0,
  `create_time` int NULL DEFAULT 0,
  `sell_price_type` int NULL DEFAULT 0,
  `sell_price` int NULL DEFAULT 0,
  `rand_property` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT '' COMMENT '随机属性',
  `lv` int NULL DEFAULT 0,
  `star` int NULL DEFAULT 0,
  `unlock_holes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT '',
  `gem_bind_equip_id` bigint NULL DEFAULT 0,
  `gem_on_equip_hole` int NULL DEFAULT 0,
  `soul_exp` int NULL DEFAULT 0 COMMENT '灵魂经验',
  `soul_level` int NULL DEFAULT 0 COMMENT '灵魂等级',
  PRIMARY KEY (`ident_id`) USING BTREE,
  INDEX `actor_bag_index`(`actor_id`, `bag_index`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_mail
-- ----------------------------
DROP TABLE IF EXISTS `player_mail`;
CREATE TABLE `player_mail`  (
  `mail_id` bigint NOT NULL DEFAULT 0,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `mail_date` int NULL DEFAULT 0,
  `readed` int NULL DEFAULT 0,
  `received` int NULL DEFAULT 0,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `contents` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `item_list` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '',
  `log_module` int NULL DEFAULT -1,
  `mail_overdue_date` int NULL DEFAULT 0,
  `item_flags` int NULL DEFAULT 0,
  `ident_id` bigint NULL DEFAULT 0,
  PRIMARY KEY (`mail_id`) USING BTREE,
  INDEX `actor_id_index`(`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_pets
-- ----------------------------
DROP TABLE IF EXISTS `player_pets`;
CREATE TABLE `player_pets`  (
  `actor_id` bigint NOT NULL,
  `pet_id` int NOT NULL DEFAULT 0,
  `lv` int NULL DEFAULT 0,
  `state` int NULL DEFAULT 0,
  `mount` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `pet_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_pets_ext
-- ----------------------------
DROP TABLE IF EXISTS `player_pets_ext`;
CREATE TABLE `player_pets_ext`  (
  `actor_id` bigint NOT NULL,
  `pool_id` int NOT NULL,
  `wish_id` int NULL DEFAULT 0,
  `wish_finished` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `pool_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_shortcut
-- ----------------------------
DROP TABLE IF EXISTS `player_shortcut`;
CREATE TABLE `player_shortcut`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `shortcut_id` int NOT NULL DEFAULT 0,
  `type` int NULL DEFAULT 0,
  `param` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `shortcut_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_skill
-- ----------------------------
DROP TABLE IF EXISTS `player_skill`;
CREATE TABLE `player_skill`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `idx` int NOT NULL DEFAULT 0 COMMENT '索引 0 主角 1 英雄',
  `skill_id` int NOT NULL DEFAULT 0,
  `level` int NULL DEFAULT 1,
  `exp` int NULL DEFAULT 0,
  `param1` int NULL DEFAULT 0,
  `param2` int NULL DEFAULT 0,
  `swit` int NULL DEFAULT 1,
  PRIMARY KEY (`actor_id`, `idx`, `skill_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for player_status
-- ----------------------------
DROP TABLE IF EXISTS `player_status`;
CREATE TABLE `player_status`  (
  `actor_id` bigint NOT NULL DEFAULT 0,
  `status_id` int NOT NULL DEFAULT 0,
  `param` int NULL DEFAULT 0,
  `duration` int NULL DEFAULT 0,
  `gap` int NULL DEFAULT 0,
  `flags` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `status_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for qihai
-- ----------------------------
DROP TABLE IF EXISTS `qihai`;
CREATE TABLE `qihai`  (
  `actor_id` bigint NOT NULL,
  `table_id` int NOT NULL,
  `level` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `table_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for quest_completed
-- ----------------------------
DROP TABLE IF EXISTS `quest_completed`;
CREATE TABLE `quest_completed`  (
  `actor_id` bigint NOT NULL,
  `completed_index` int NOT NULL,
  `completed_value` int NULL DEFAULT NULL,
  PRIMARY KEY (`actor_id`, `completed_index`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for quest_data
-- ----------------------------
DROP TABLE IF EXISTS `quest_data`;
CREATE TABLE `quest_data`  (
  `actor_id` bigint NOT NULL,
  `quest_id` int NOT NULL,
  `receive_time` int NULL DEFAULT 0,
  `state` int NULL DEFAULT 0,
  `map_use_item_id` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `quest_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for quest_goal_data
-- ----------------------------
DROP TABLE IF EXISTS `quest_goal_data`;
CREATE TABLE `quest_goal_data`  (
  `actor_id` bigint NOT NULL,
  `quest_id` int NOT NULL,
  `goal_index` int NOT NULL,
  `table_index` int NULL DEFAULT 0,
  `goal_got_count` int NULL DEFAULT 0,
  `goal_id` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `quest_id`, `goal_index`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for quest_repeat
-- ----------------------------
DROP TABLE IF EXISTS `quest_repeat`;
CREATE TABLE `quest_repeat`  (
  `actor_id` bigint NOT NULL,
  `quest_id` int NOT NULL,
  `completed_time` int NULL DEFAULT 0,
  `completed_count` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `quest_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for relationship
-- ----------------------------
DROP TABLE IF EXISTS `relationship`;
CREATE TABLE `relationship`  (
  `actor_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tar_actor_id` bigint NOT NULL DEFAULT 0,
  `rel_type` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `tar_actor_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4294977603 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sect
-- ----------------------------
DROP TABLE IF EXISTS `sect`;
CREATE TABLE `sect`  (
  `sect_id` bigint NOT NULL,
  `sect_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `notice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `level` int NULL DEFAULT 1,
  `gold` bigint NULL DEFAULT 0,
  `vcoin` int NULL DEFAULT 0,
  `item_number` int NULL DEFAULT 0,
  `auto_join` int NULL DEFAULT 0,
  `auto_create` int NULL DEFAULT 0,
  `country` int NULL DEFAULT 0 COMMENT '国家',
  PRIMARY KEY (`sect_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sect_member
-- ----------------------------
DROP TABLE IF EXISTS `sect_member`;
CREATE TABLE `sect_member`  (
  `sect_id` bigint NOT NULL,
  `actor_id` bigint NOT NULL,
  `title` int NULL DEFAULT 103,
  `job` int NULL DEFAULT 100,
  `level` int NULL DEFAULT 1,
  `gender` int NULL DEFAULT 200,
  `player_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `contribute` int NULL DEFAULT 0,
  `speak` int NULL DEFAULT 0,
  PRIMARY KEY (`sect_id`, `actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sect_redpacket
-- ----------------------------
DROP TABLE IF EXISTS `sect_redpacket`;
CREATE TABLE `sect_redpacket`  (
  `sect_id` bigint NOT NULL,
  `pac_id` bigint NOT NULL,
  `sender_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `remain_size` int NULL DEFAULT 0,
  `remain_value` int NULL DEFAULT 0,
  `send_time` int NULL DEFAULT 0,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  `total_size` int NULL DEFAULT NULL,
  PRIMARY KEY (`pac_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sect_redpacket_info
-- ----------------------------
DROP TABLE IF EXISTS `sect_redpacket_info`;
CREATE TABLE `sect_redpacket_info`  (
  `red_pack_id` bigint NOT NULL,
  `sect_id` bigint NULL DEFAULT 0,
  `actor_id` bigint NOT NULL DEFAULT 0,
  `actor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `number` int NULL DEFAULT NULL,
  PRIMARY KEY (`red_pack_id`, `actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for server_refurbish
-- ----------------------------
DROP TABLE IF EXISTS `server_refurbish`;
CREATE TABLE `server_refurbish`  (
  `server_id` bigint NOT NULL,
  `daily_refurbish_time` bigint NULL DEFAULT 0,
  `weekly_refurbish_time` bigint NULL DEFAULT 0,
  `monthly_refurbish_time` bigint NULL DEFAULT 0,
  PRIMARY KEY (`server_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for shop
-- ----------------------------
DROP TABLE IF EXISTS `shop`;
CREATE TABLE `shop`  (
  `actor_id` bigint NOT NULL,
  `shop_id` int NOT NULL DEFAULT 0 COMMENT '商店id',
  `update_time` bigint NULL DEFAULT 0 COMMENT '刷新时间',
  PRIMARY KEY (`actor_id`, `shop_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for title
-- ----------------------------
DROP TABLE IF EXISTS `title`;
CREATE TABLE `title`  (
  `actor_id` bigint NOT NULL,
  `title_id` int NOT NULL,
  `limit_time` int NULL DEFAULT 0,
  PRIMARY KEY (`actor_id`, `title_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for title_select
-- ----------------------------
DROP TABLE IF EXISTS `title_select`;
CREATE TABLE `title_select`  (
  `actor_id` bigint NOT NULL,
  `title_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for top_list
-- ----------------------------
DROP TABLE IF EXISTS `top_list`;
CREATE TABLE `top_list`  (
  `actor_id` bigint NOT NULL,
  `job` int NULL DEFAULT 0,
  `gender` int NULL DEFAULT 0,
  `sect_id` bigint NULL DEFAULT 0,
  `cloth` int NULL DEFAULT -1,
  `weapon` int NULL DEFAULT -1,
  `hair` int NULL DEFAULT -1,
  `lv` int NULL DEFAULT 0,
  `trial_tower_floor` int NULL DEFAULT 0,
  `assist_data` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '',
  PRIMARY KEY (`actor_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Procedure structure for exp_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `exp_update`;
delimiter ;;
CREATE PROCEDURE `exp_update`(IN `nactor_id` bigint,IN `nexp` bigint)
BEGIN update player set exp = nexp where actor_id = nactor_id limit 1; END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for loadactorslistbyaccount
-- ----------------------------
DROP PROCEDURE IF EXISTS `loadactorslistbyaccount`;
delimiter ;;
CREATE PROCEDURE `loadactorslistbyaccount`(in nAccountid Integer(32))
BEGIN select a.actorid,a.nickname,a.level,a.job,a.gender,a.online,b.equip_have from player as a left join player_item as b on a.actorid = b.actorid WHERE a.accountid = nAccountid order by a.level desc; END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_achieve_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_achieve_update`;
delimiter ;;
CREATE PROCEDURE `player_achieve_update`(IN `nachieve_id` int,IN `nactor_id` bigint,IN `nstate` int,IN `nparam` int)
BEGIN REPLACE  INTO player_achieve(achieve_id,actor_id,state,param)VALUES(nachieve_id,nactor_id,nstate,nparam); END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_buff_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_buff_update`;
delimiter ;;
CREATE PROCEDURE `player_buff_update`(IN `nid` int,IN `nactor_id` bigint,IN `nend_val` int)
BEGIN REPLACE  INTO player_buff(id,actor_id,end_val)VALUES(nid,nactor_id,nend_val); END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_gift_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_gift_update`;
delimiter ;;
CREATE PROCEDURE `player_gift_update`(IN `nid` int,IN `nactor_id` bigint,IN `nused` int,IN `nbind` int,IN `nnum` int,IN `ntype_id` int,IN `smsg` varchar(255))
BEGIN  REPLACE  INTO player_gift(id,actor_id,used,bind,num,type_id,msg)VALUES(nid,nactor_id,nused,nbind,nnum,ntype_id,smsg); END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_item_bag_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_item_bag_update`;
delimiter ;;
CREATE PROCEDURE `player_item_bag_update`(IN `nident_id` bigint,IN `nactor_id` bigint,IN `nbag_index` int,IN `nposition` int,IN `ntype_id` int,IN `nduration` int,IN `ndura_max` int,IN `nitem_flags` int,IN `nluck` int,IN `nnumber` int,IN `ncreate_Time` int,IN `nprotect` int,IN `nsell_price_type` int,IN `nsell_price` int,IN `nz_level` int,IN `ninject_exp` int,IN `nadd_ac` int,IN `nadd_mac` int,IN `nadd_dc` int,IN `nadd_mc` int,IN `nadd_sc` int,IN `nadd_accuracy` int,IN `nadd_dodge` int,IN `nadd_hp` int,IN `nadd_mp` int,IN `nadd_baoji_prob` int,IN `nadd_baoji_pres` int,IN `nadd_tenacity` int,IN `nfloat_prop` int)
BEGIN
REPLACE  INTO player_item(ident_id,actor_id,bag_index,position,type_id,duration,dura_max,item_flags,luck,number,create_Time,
protect,sell_price_type,sell_price,z_level,inject_exp,add_ac,add_mac,add_dc,add_mc,add_sc,add_accuracy,add_dodge,add_hp,add_mp,add_baoji_prob,add_baoji_pres,add_tenacity,float_prop)
VALUES(nident_id,nactor_id,nbag_index,nposition,ntype_id,nduration,ndura_max,nitem_flags,nluck,nnumber,ncreate_Time,nprotect,nsell_price_type,nsell_price,nz_level,ninject_exp,nadd_ac,nadd_mac,
nadd_dc,nadd_mc,nadd_sc,nadd_accuracy,nadd_dodge,nadd_hp,nadd_mp,nadd_baoji_prob,nadd_baoji_pres,nadd_tenacity,nfloat_prop);
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_mail_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_mail_update`;
delimiter ;;
CREATE PROCEDURE `player_mail_update`(IN `nid` bigint,IN `nactor_id` bigint,IN `nmail_date` int,IN `nreaded` int,IN `nreceived` int,IN `stitle` varchar(255),IN `scontents` varchar(255),IN `sitem_list` varchar(255))
BEGIN 
REPLACE  INTO player_mail(mail_id,actor_id,mail_date,readed,received,title,contents,item_list)VALUES(nid,nactor_id,nmail_date,nreaded,nreceived,stitle,scontents,sitem_list); 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_shortcut_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_shortcut_update`;
delimiter ;;
CREATE PROCEDURE `player_shortcut_update`(IN `nshortcut_id` int,IN `nactor_id` bigint,IN `ntype` int,IN `nparam` int)
BEGIN 
REPLACE  INTO player_shortcut(shortcut_id,actor_id,type,param)VALUES(nshortcut_id,nactor_id,ntype,nparam); 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_skill_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_skill_update`;
delimiter ;;
CREATE PROCEDURE `player_skill_update`(IN `nskill_id` int,IN `nactor_id` bigint,IN `nlevel` int,IN `nexp` int,IN `nparam1` int)
BEGIN 
REPLACE  INTO player_skill(skill_id,actor_id,level,exp,param1)VALUES(nskill_id,nactor_id,nlevel,nexp,nparam1); 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for player_status_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `player_status_update`;
delimiter ;;
CREATE PROCEDURE `player_status_update`(IN `nstatus_id` int,IN `nactor_id` bigint,IN `nparam` int,IN `nduration` int,IN `ngap` int,IN `nflags` int)
BEGIN 
REPLACE  INTO player_status(status_id,actor_id,param,duration,gap,flags)VALUES(nstatus_id,nactor_id,nparam,nduration,ngap,nflags); 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for postmailtoallplayer
-- ----------------------------
DROP PROCEDURE IF EXISTS `postmailtoallplayer`;
delimiter ;;
CREATE PROCEDURE `postmailtoallplayer`(pam_mail_id int(20),pam_date int(11),pam_readed int(6),pam_received int(6),pam_title varchar(255),pam_contents varchar(1024),pam_item_list varchar(512))
BEGIN 
insert into player_mail(mail_id,actor_id,mail_date,readed,received,title,contents,item_list) SELECT (@i := @i + 1),actor_id,pam_date,pam_readed,pam_received,pam_title,pam_contents,pam_item_list from player; 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for relationship_update
-- ----------------------------
DROP PROCEDURE IF EXISTS `relationship_update`;
delimiter ;;
CREATE PROCEDURE `relationship_update`(IN `nactor_id` bigint,IN `srel_seed_name` varchar(255),IN `ntitle` int)
BEGIN 
REPLACE  INTO relationship(actor_id,rel_seed_name,title)VALUES(nactor_id,srel_seed_name,ntitle); 
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for sect_item_insert
-- ----------------------------
DROP PROCEDURE IF EXISTS `sect_item_insert`;
delimiter ;;
CREATE PROCEDURE `sect_item_insert`(IN `nident_id` bigint,IN `nsect_id` bigint,IN `ntype_id` int,IN `nitem_flags` int,IN `nluck` int,IN `nnumber` int,IN `ncreate_Time` int,IN `nprotect` int,IN `nz_level` int,IN `ninject_exp` int,IN `nadd_ac` int,IN `nadd_mac` int,IN `nadd_dc` int,IN `nadd_mc` int,IN `nadd_sc` int,IN `nadd_accuracy` int,IN `nadd_dodge` int,IN `nadd_hp` int,IN `nadd_mp` int,IN `nadd_baoji_prob` int,IN `nadd_baoji_pres` int,IN `nadd_tenacity` int,IN `nfloat_prop` int)
BEGIN 
INSERT  INTO sect_item(ident_id,sect_id,type_id,item_flags,luck,number,create_Time,protect,z_level,inject_exp,add_ac,add_mac,add_dc,add_mc,add_sc,add_accuracy,add_dodge,add_hp,add_mp,add_baoji_prob,add_baoji_pres,add_tenacity,float_prop)
VALUES(nident_id,nsect_id,ntype_id,nitem_flags,nluck,nnumber,ncreate_Time,nprotect,nz_level,ninject_exp,nadd_ac,nadd_mac,
nadd_dc,nadd_mc,nadd_sc,nadd_accuracy,nadd_dodge,nadd_hp,nadd_mp,nadd_baoji_prob,nadd_baoji_pres,nadd_tenacity,nfloat_prop); 
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
