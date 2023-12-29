/*
Navicat MySQL Data Transfer

Source Server         : 正式服
Source Server Version : 50730
Source Host           : rm-bp1ui9f0unm80e9jj.mysql.rds.aliyuncs.com:3306
Source Database       : agent_db

Target Server Type    : MYSQL
Target Server Version : 50730
File Encoding         : 65001

Date: 2021-12-28 15:25:37
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `dl_action_log`
-- ----------------------------
DROP TABLE IF EXISTS `dl_action_log`;
CREATE TABLE `dl_action_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL COMMENT '代理账号',
  `action_desc` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '操作记录描述',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_action_log
-- ----------------------------
INSERT INTO `dl_action_log` VALUES ('214', '1', '添加代理用户!', '1613788978', '1');
INSERT INTO `dl_action_log` VALUES ('215', '24', '登陆成功。用户名:13858868309', '1613789075', '1');
INSERT INTO `dl_action_log` VALUES ('216', '0', '登录失败，尝试登录。用户名：13858868309', '1613789744', '1');
INSERT INTO `dl_action_log` VALUES ('217', '24', '登陆成功。用户名:13858868309', '1613789755', '1');
INSERT INTO `dl_action_log` VALUES ('218', '1', '登陆成功。用户名:admin', '1613791900', '1');
INSERT INTO `dl_action_log` VALUES ('219', '2', '代理用户ID:2访问授权页面成功!', '1613791984', '1');
INSERT INTO `dl_action_log` VALUES ('220', '24', '登陆成功。用户名:13858868309', '1613792003', '1');
INSERT INTO `dl_action_log` VALUES ('221', '1', '登陆成功。用户名:admin', '1613792037', '1');
INSERT INTO `dl_action_log` VALUES ('222', '24', '登陆成功。用户名:13858868309', '1613792171', '1');
INSERT INTO `dl_action_log` VALUES ('223', '24', '登陆成功。用户名:13858868309', '1613800019', '1');
INSERT INTO `dl_action_log` VALUES ('224', '24', '登陆成功。用户名:13858868309', '1613800064', '1');
INSERT INTO `dl_action_log` VALUES ('225', '1', '登陆成功。用户名:admin', '1613800139', '1');
INSERT INTO `dl_action_log` VALUES ('226', '2', '代理用户ID:2访问授权页面成功!', '1613800153', '1');
INSERT INTO `dl_action_log` VALUES ('227', '24', '登陆成功。用户名:13858868309', '1613800168', '1');
INSERT INTO `dl_action_log` VALUES ('228', '24', '登陆成功。用户名:13858868309', '1613800202', '1');
INSERT INTO `dl_action_log` VALUES ('229', '24', '登陆成功。用户名:13858868309', '1613800263', '1');
INSERT INTO `dl_action_log` VALUES ('230', '24', '登陆成功。用户名:13858868309', '1613800409', '1');
INSERT INTO `dl_action_log` VALUES ('231', '24', '登陆成功。用户名:13858868309', '1613800433', '1');
INSERT INTO `dl_action_log` VALUES ('232', '24', '登陆成功。用户名:13858868309', '1613800516', '1');
INSERT INTO `dl_action_log` VALUES ('233', '24', '登陆成功。用户名:13858868309', '1613800644', '1');
INSERT INTO `dl_action_log` VALUES ('234', '24', '登陆成功。用户名:13858868309', '1613800856', '1');
INSERT INTO `dl_action_log` VALUES ('235', '1', '登陆成功。用户名:admin', '1613800879', '1');
INSERT INTO `dl_action_log` VALUES ('236', '1', '登陆成功。用户名:admin', '1613897394', '1');
INSERT INTO `dl_action_log` VALUES ('237', '1', '登陆成功。用户名:admin', '1614305131', '1');
INSERT INTO `dl_action_log` VALUES ('238', '1', '添加代理用户!', '1614305300', '1');
INSERT INTO `dl_action_log` VALUES ('239', '0', '登录失败，尝试登录。用户名：admin', '1614305494', '1');
INSERT INTO `dl_action_log` VALUES ('240', '1', '登陆成功。用户名:admin', '1614305502', '1');
INSERT INTO `dl_action_log` VALUES ('241', '0', '5223', '1614306329', '1');
INSERT INTO `dl_action_log` VALUES ('242', '1', '添加代理用户!', '1614310354', '1');
INSERT INTO `dl_action_log` VALUES ('243', '0', 'activation_code', '1614310384', '1');
INSERT INTO `dl_action_log` VALUES ('244', '0', 'activation_code', '1614310391', '1');
INSERT INTO `dl_action_log` VALUES ('245', '1', '添加代理用户!', '1614310502', '1');
INSERT INTO `dl_action_log` VALUES ('246', '1', '添加代理用户!', '1614311918', '1');
INSERT INTO `dl_action_log` VALUES ('247', '1', '登陆成功。用户名:admin', '1614331126', '1');
INSERT INTO `dl_action_log` VALUES ('248', '1', '登陆成功。用户名:admin', '1614389770', '1');
INSERT INTO `dl_action_log` VALUES ('249', '0', 'menu', '1614389786', '1');
INSERT INTO `dl_action_log` VALUES ('250', '1', '添加代理用户!', '1614407359', '1');
INSERT INTO `dl_action_log` VALUES ('251', '1', '添加代理用户!', '1614407480', '1');
INSERT INTO `dl_action_log` VALUES ('252', '1', '添加代理用户!', '1614407563', '1');
INSERT INTO `dl_action_log` VALUES ('253', '1', '添加代理用户!', '1614407836', '1');
INSERT INTO `dl_action_log` VALUES ('254', '1', '登陆成功。用户名:admin', '1614409671', '1');
INSERT INTO `dl_action_log` VALUES ('255', '1', '登陆成功。用户名:admin', '1614577425', '1');

-- ----------------------------
-- Table structure for `dl_activation_code`
-- ----------------------------
DROP TABLE IF EXISTS `dl_activation_code`;
CREATE TABLE `dl_activation_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '激活码',
  `type` int(10) DEFAULT '1' COMMENT '激活码类型（1下载注册礼包，2关注公众号礼包）',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态(默认1正常，-1删除或禁用)',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '修改时间',
  `item_reward` varchar(255) DEFAULT '' COMMENT '奖励物品',
  `agent_id` int(11) DEFAULT '0' COMMENT '代理id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5228 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_activation_code
-- ----------------------------
INSERT INTO `dl_activation_code` VALUES ('5222', '20210220cyT6VBxy', '1', '0', '1613788978', '0', '11100002|10', '24');
INSERT INTO `dl_activation_code` VALUES ('5223', '20210226hiOSyA6Z', '1', '-1', '1614305301', '1614310391', '11100006|1', '25');
INSERT INTO `dl_activation_code` VALUES ('5224', '20210226DCqO66Cr', '1', '-1', '1614310354', '1614310384', '11102087|1', '26');
INSERT INTO `dl_activation_code` VALUES ('5225', '202102268NL4a2Sk', '1', '0', '1614310502', '0', '11102087|1', '27');
INSERT INTO `dl_activation_code` VALUES ('5226', '20210226utOV3O4V', '1', '0', '1614311918', '0', '11112135|1;11112134|2', '28');
INSERT INTO `dl_activation_code` VALUES ('5227', '20210227PzkAViuZ', '1', '0', '1614407846', '0', '11100001|1;11101015|1;11101040|4;11101070|1', '32');

-- ----------------------------
-- Table structure for `dl_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `dl_auth_group`;
CREATE TABLE `dl_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型',
  `title` char(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_auth_group
-- ----------------------------
INSERT INTO `dl_auth_group` VALUES ('1', 'admin', '1', '管理员', '管理员用户组', '1', '');
INSERT INTO `dl_auth_group` VALUES ('2', 'index', '1', '代理用户组', '代理用户组', '1', '1,24,19,20,17,18,21');

-- ----------------------------
-- Table structure for `dl_cash`
-- ----------------------------
DROP TABLE IF EXISTS `dl_cash`;
CREATE TABLE `dl_cash` (
  `id` int(11) NOT NULL,
  `amount` int(11) NOT NULL COMMENT '提现金额',
  `type` int(11) NOT NULL COMMENT '提现方式（0：支付宝，1：微信，2：银行卡）',
  `agent_id` int(11) NOT NULL COMMENT '代理用户ID',
  `account` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '提现账户',
  `account_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '账户姓名',
  `telphone` varchar(11) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机号码',
  `bank_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '银行名称',
  `open_bank` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '开户行',
  `apply_time` int(11) DEFAULT NULL COMMENT '申请时间',
  `payment_time` int(11) DEFAULT NULL COMMENT '打款时间',
  `status` int(11) DEFAULT NULL COMMENT '(状态，0：申请中，1已打款，2已驳回，-1已删除)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_cash
-- ----------------------------

-- ----------------------------
-- Table structure for `dl_config`
-- ----------------------------
DROP TABLE IF EXISTS `dl_config`;
CREATE TABLE `dl_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `extra` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  `value` text CHARACTER SET utf8 COMMENT '配置值',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `module` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0全部\r\n1前台\r\n2后台',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_config
-- ----------------------------
INSERT INTO `dl_config` VALUES ('1', 'WEB_SITE_TITLE', '1', '网站标题', '1', '', '网站标题前台显示标题', '1378898976', '1588951530', '1', 'HulaCWMS-青岛甘木文化传媒有限公司-呼啦企业网站管理系统演示', '0', '1');
INSERT INTO `dl_config` VALUES ('2', 'WEB_SITE_DESCRIPTION', '2', '网站描述', '1', '', '网站搜索引擎描述', '1378898976', '1588951534', '1', 'HulaCWMS(呼啦企业网站管理系统)诞生于2019年8月（www.hulaxz.com），是自主研发的一套专门用户管理企业网站的管理系统，以提供分享精品呼啦源码及建站过程常遇到的问题解决方案汇总为主要宗旨。系统特点：清爽美观，系统做减法，用户体验就很棒。', '1', '1');
INSERT INTO `dl_config` VALUES ('3', 'WEB_SITE_KEYWORD', '2', '网站关键字', '1', '', '网站搜索引擎关键字', '1378898976', '1588951539', '1', 'HulaCWMS,呼啦企业网站管理系统演示,cms,内容管理系统', '8', '1');
INSERT INTO `dl_config` VALUES ('4', 'WEB_SITE_CLOSE', '4', '关闭站点', '1', '0:关闭,1:开启', '站点关闭后用户不能访问', '1378898976', '1588951296', '1', '1', '1', '1');
INSERT INTO `dl_config` VALUES ('5', 'CONFIG_TYPE_LIST', '3', '配置类型列表', '0', '', '主要用于数据解析和页面表单的生成', '1378898976', '1588951305', '1', '0:数字\n1:字符\n5:图片\n2:文本\n3:数组\n4:枚举', '2', '2');
INSERT INTO `dl_config` VALUES ('6', 'WEB_SITE_ICP', '1', '网站备案号', '1', '', '设置在网站底部显示的备案号，如：“鲁ICP备00000001号-1”', '1378900335', '1592625538', '1', '鲁ICP备00000001号-1', '9', '1');
INSERT INTO `dl_config` VALUES ('7', 'CONFIG_MODULE', '3', '配置作用域', '0', '', '在管理配置时，可以设置该配置项的所在作用域。', '1588949815', '1588951419', '1', '0:全站作用\n1:仅前台作用\n2:仅后台作用', '0', '2');
INSERT INTO `dl_config` VALUES ('8', 'COMPANY_TEL', '1', '公司联系电话', '3', '', '公司联系电话，用于前台展示', '1571490284', '1588951334', '1', '010-123456', '0', '1');
INSERT INTO `dl_config` VALUES ('9', 'COMPANY_EMAIL', '1', '公司联系邮箱', '3', '', '公司联系邮箱，用于前台展示', '1571490326', '1588951587', '1', 'zz@zhuopro.com', '0', '1');
INSERT INTO `dl_config` VALUES ('10', 'CONFIG_GROUP_LIST', '3', '配置分组', '0', '', '配置分组', '1379228036', '1588951434', '1', '1:基本\n3:联系\n4:系统', '4', '2');
INSERT INTO `dl_config` VALUES ('11', 'LIST_ROWS', '0', '后台每页记录数', '0', '', '后台数据每页显示记录数', '1379503896', '1588951440', '1', '20', '10', '2');
INSERT INTO `dl_config` VALUES ('12', 'COMPANY_ADD', '0', '公司联系地址', '3', '', '公司联系地址，用于前台展示', '1571490386', '1588951450', '1', '青岛市黄岛区长江路街道', '0', '0');
INSERT INTO `dl_config` VALUES ('13', 'COMPANY_QQ', '1', '联系QQ', '3', '', '公司联系QQ，用于前台显示', '1574838586', '1588951607', '1', '123456', '0', '1');
INSERT INTO `dl_config` VALUES ('14', 'DATA_BACKUP_PATH', '1', '数据库备份根路径', '4', '', '路径必须以 / 结尾', '1381482411', '1588951614', '1', './databack/', '5', '2');
INSERT INTO `dl_config` VALUES ('15', 'DATA_BACKUP_PART_SIZE', '0', '数据库备份卷大小', '4', '', '该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M', '1381482488', '1588951620', '1', '20971520', '7', '2');
INSERT INTO `dl_config` VALUES ('16', 'DATA_BACKUP_COMPRESS', '4', '数据库备份文件是否启用压缩', '4', '0:不压缩\n1:启用压缩', '压缩备份文件需要PHP环境支持gzopen,gzwrite函数', '1381713345', '1588951634', '1', '1', '9', '2');
INSERT INTO `dl_config` VALUES ('17', 'DATA_BACKUP_COMPRESS_LEVEL', '4', '数据库备份文件压缩级别', '4', '1:普通\n4:一般\n9:最高', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '1381713408', '1588951639', '1', '9', '10', '2');
INSERT INTO `dl_config` VALUES ('18', 'DEVELOP_MODE', '4', '开启开发者模式', '4', '0:关闭\n1:开启', '是否开启开发者模式', '1383105995', '1588951648', '1', '1', '11', '2');
INSERT INTO `dl_config` VALUES ('19', 'WEB_TEMPLATE_PATH', '0', '网站前台模板目录', '0', '', '相对路径，必须以字母或数字开头', '1571313339', '1591866150', '1', 'default', '0', '0');
INSERT INTO `dl_config` VALUES ('20', 'WEB_POWERBY', '0', '网站版权', '0', '', '网站版权，用于前台显示', '1571490683', '1588951665', '1', 'power by HulaCWMS 灼灼文化', '0', '1');
INSERT INTO `dl_config` VALUES ('21', 'WEB_REWRITE', '4', '开启伪静态', '0', '0:关闭,1:开启', '开启伪静态，url会省略入口文件index.php，但它必须依赖服务器配置。否则网站无法正常访问', '1571491840', '1592882330', '1', '0', '0', '1');
INSERT INTO `dl_config` VALUES ('22', 'WEB_TONGJI', '4', '开启网站统计', '0', '0:关闭,1:开启', '是否开始网站统计', '1587980376', '1588951709', '1', '1', '0', '0');

-- ----------------------------
-- Table structure for `dl_guild`
-- ----------------------------
DROP TABLE IF EXISTS `dl_guild`;
CREATE TABLE `dl_guild` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL COMMENT '代理ID',
  `guild_name` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '公会名称',
  `status` int(11) DEFAULT NULL COMMENT '状态（1：启用:2：禁用，-1：已删除）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_guild
-- ----------------------------
INSERT INTO `dl_guild` VALUES ('17', '24', '官方公会', '1');
INSERT INTO `dl_guild` VALUES ('18', '25', '111111', '1');
INSERT INTO `dl_guild` VALUES ('19', '26', '22', '1');
INSERT INTO `dl_guild` VALUES ('20', '27', '2222', '1');
INSERT INTO `dl_guild` VALUES ('21', '28', '33', '1');
INSERT INTO `dl_guild` VALUES ('22', '29', '火车站联盟', '1');
INSERT INTO `dl_guild` VALUES ('23', '30', '飞机场联盟', '1');
INSERT INTO `dl_guild` VALUES ('24', '31', '东站联盟', '1');
INSERT INTO `dl_guild` VALUES ('25', '32', '二狗子联盟', '1');

-- ----------------------------
-- Table structure for `dl_member`
-- ----------------------------
DROP TABLE IF EXISTS `dl_member`;
CREATE TABLE `dl_member` (
  `agent_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属代理ID',
  `user_id` bigint(11) NOT NULL DEFAULT '0' COMMENT '玩家ID',
  `total_value` bigint(11) DEFAULT '0',
  PRIMARY KEY (`user_id`,`agent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_member
-- ----------------------------
INSERT INTO `dl_member` VALUES ('1', '0', '0');
INSERT INTO `dl_member` VALUES ('28', '309', '0');
INSERT INTO `dl_member` VALUES ('1', '440', '0');

-- ----------------------------
-- Table structure for `dl_menu`
-- ----------------------------
DROP TABLE IF EXISTS `dl_menu`;
CREATE TABLE `dl_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '提示',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `icon` char(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '菜单图标',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_menu
-- ----------------------------
INSERT INTO `dl_menu` VALUES ('1', '后台首页', '0', '1', 'index/main', '0', '', '0', 'layui-icon-home', '1');
INSERT INTO `dl_menu` VALUES ('2', '后台管理', '0', '88', '', '0', '', '0', 'layui-icon-auz', '1');
INSERT INTO `dl_menu` VALUES ('3', '权限管理', '2', '2', 'auth/index', '0', '', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('4', '操作日志', '2', '4', 'actionlog/index', '0', '', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('5', '系统管理', '0', '99', '', '0', '', '0', 'layui-icon-set', '1');
INSERT INTO `dl_menu` VALUES ('6', '配置管理', '5', '2', 'config/index', '0', '', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('7', '菜单管理', '5', '3', 'menu/index', '0', '', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('8', '数据库管理', '5', '4', 'databases/index?type=export', '0', '', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('9', '备份', '10', '0', 'databases/export', '0', '备份数据库', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('10', '优化表', '10', '0', 'databases/optimize', '0', '优化数据表', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('11', '修复表', '10', '0', 'databases/repair', '0', '修复数据表', '0', '', '1');
INSERT INTO `dl_menu` VALUES ('12', '数据统计', '0', '10', '', '0', '数据统计模块', '0', 'layui-icon-chart', '1');
INSERT INTO `dl_menu` VALUES ('15', '代理管理', '0', '4', '', '0', '代理管理', '0', 'layui-icon-user', '1');
INSERT INTO `dl_menu` VALUES ('16', '代理用户', '15', '15', 'agent/index', '0', '代理用户', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('17', '提现管理', '0', '5', '', '0', '提现管理', '0', 'layui-icon-rmb', '1');
INSERT INTO `dl_menu` VALUES ('18', '提现记录', '17', '17', 'cash/index', '0', '提现记录', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('19', '公会管理', '0', '3', '', '0', '公会管理', '0', 'layui-icon-group', '1');
INSERT INTO `dl_menu` VALUES ('20', '公会成员', '19', '99', 'guild/member', '0', '公会成员', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('21', '申请提现', '17', '25', 'cash/apply', '1', '代理申请提现', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('22', '公会列表', '19', '21', 'guild/index', '0', '公会列表', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('23', '代理分成', '15', '25', 'share/index', '0', '代理分成', '0', null, '1');
INSERT INTO `dl_menu` VALUES ('24', '代理后台首页', '0', '2', 'index/main2', '1', '代理后台首页', '0', 'layui-icon-home', '1');
INSERT INTO `dl_menu` VALUES ('25', '激活码', '0', '6', '', '0', '激活码', '0', 'layui-icon-water', '1');
INSERT INTO `dl_menu` VALUES ('26', '激活码列表', '25', '99', 'activation_code/index', '0', '激活码列表', '0', null, '1');

-- ----------------------------
-- Table structure for `dl_pv_log`
-- ----------------------------
DROP TABLE IF EXISTS `dl_pv_log`;
CREATE TABLE `dl_pv_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) NOT NULL DEFAULT '0' COMMENT '时间段',
  `view` int(10) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `date` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '访问时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_pv_log
-- ----------------------------

-- ----------------------------
-- Table structure for `dl_share`
-- ----------------------------
DROP TABLE IF EXISTS `dl_share`;
CREATE TABLE `dl_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guild_id` int(11) DEFAULT '0' COMMENT '公会ID',
  `guild_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '工会名称',
  `agent_id` int(11) DEFAULT '0' COMMENT '代理用户ID',
  `agent_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户名称',
  `origin_share_rate` int(11) DEFAULT '0' COMMENT '原分成比例',
  `share_rate` int(11) DEFAULT '0' COMMENT '分成比例',
  `amount` bigint(11) DEFAULT '0' COMMENT '分成总金额',
  `extract` int(11) DEFAULT '0' COMMENT '已提现金额',
  `balance` int(11) DEFAULT '0' COMMENT '余额',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0',
  `promote_link` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '推广链接地址',
  `promote_qrcode` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '推广二维码',
  `promote_code` char(6) NOT NULL COMMENT '推广码、邀请码、激活码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_share
-- ----------------------------
INSERT INTO `dl_share` VALUES ('7', '17', '官方公会', '24', '官方代理', '0', '85', '0', '0', '0', '1613788978', '0', 'http://www.52yiwan.com?agent_id=24', '/qrcode/20210220104258.png', '268531');
INSERT INTO `dl_share` VALUES ('8', '18', '111111', '25', '陈倩', '0', '1', '0', '0', '0', '1614305301', '0', 'http://www.52yiwan.com?agent_id=25', '/qrcode/20210226100820.png', '363005');
INSERT INTO `dl_share` VALUES ('9', '19', '22', '26', '水电费', '0', '1', '0', '0', '0', '1614310354', '0', 'http://www.52yiwan.com?agent_id=26', '/qrcode/20210226113234.png', '293172');
INSERT INTO `dl_share` VALUES ('10', '20', '2222', '27', '洒洒水', '0', '1', '0', '0', '0', '1614310502', '0', 'http://www.52yiwan.com?agent_id=27', '/qrcode/20210226113502.png', '882474');
INSERT INTO `dl_share` VALUES ('11', '21', '33', '28', '老水电费', '0', '1', '0', '0', '0', '1614311918', '0', 'http://www.52yiwan.com?agent_id=28', '/qrcode/20210226115838.png', '324624');
INSERT INTO `dl_share` VALUES ('12', '22', '火车站联盟', '29', '火车站代理', '0', '80', '0', '0', '0', '1614407359', '0', 'http://www.52yiwan.com?agent_id=29', '/qrcode/20210227022919.png', '621927');
INSERT INTO `dl_share` VALUES ('13', '23', '飞机场联盟', '30', '飞机场代理', '0', '80', '0', '0', '0', '1614407484', '0', 'http://www.52yiwan.com?agent_id=30', '/qrcode/20210227023124.png', '605681');
INSERT INTO `dl_share` VALUES ('14', '24', '东站联盟', '31', '东站代理', '0', '60', '0', '0', '0', '1614407564', '0', 'http://www.52yiwan.com?agent_id=31', '/qrcode/20210227023244.png', '310314');
INSERT INTO `dl_share` VALUES ('15', '25', '二狗子联盟', '32', '二狗子', '0', '75', '0', '0', '0', '1614407838', '0', 'http://www.52yiwan.com?agent_id=32', '/qrcode/20210227023718.png', '226763');

-- ----------------------------
-- Table structure for `dl_url_log`
-- ----------------------------
DROP TABLE IF EXISTS `dl_url_log`;
CREATE TABLE `dl_url_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'url 受访的页面url',
  `pv` int(10) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `title` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '页面名称',
  `date` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '访问时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_url_log
-- ----------------------------

-- ----------------------------
-- Table structure for `dl_users`
-- ----------------------------
DROP TABLE IF EXISTS `dl_users`;
CREATE TABLE `dl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码',
  `group_id` int(11) DEFAULT '0' COMMENT '用户组',
  `realname` varchar(20) CHARACTER SET utf8 DEFAULT '' COMMENT '代理姓名',
  `telphone` varchar(11) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '手机号码',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态（0：启用，1禁用，-1删除）',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `last_login_ip` varchar(11) CHARACTER SET utf8 DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int(11) DEFAULT '0' COMMENT '最后登录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_users
-- ----------------------------
INSERT INTO `dl_users` VALUES ('1', 'admin', 'd93a5def7511da3d0f2d171d9c344e91', '0', 'admin', '13888888888', '1', '1598258382', '0', '127.0.0.1', '1614577425');
INSERT INTO `dl_users` VALUES ('24', '13858868309', 'd93a5def7511da3d0f2d171d9c344e91', '2', '官方代理', '13858868309', '1', '1613788978', '0', '127.0.0.1', '1613800856');
INSERT INTO `dl_users` VALUES ('25', '132444455445', 'c78b6663d47cfbdb4d65ea51c104044e', '2', '陈倩', '13244445544', '1', '1614305300', '0', '', '0');
INSERT INTO `dl_users` VALUES ('26', '132444455446', '3934145698911456e8b4a89a20e6cd4b', '0', '水电费', '13244445544', '1', '1614310354', '0', '', '0');
INSERT INTO `dl_users` VALUES ('27', '132444455447', 'c78b6663d47cfbdb4d65ea51c104044e', '2', '洒洒水', '13244445544', '1', '1614310502', '0', '', '0');
INSERT INTO `dl_users` VALUES ('28', '132444455448', 'c78b6663d47cfbdb4d65ea51c104044e', '2', '老水电费', '13244445544', '1', '1614311918', '0', '', '0');
INSERT INTO `dl_users` VALUES ('29', '13666689745', 'd93a5def7511da3d0f2d171d9c344e91', '2', '火车站代理', '13666689745', '1', '1614407359', '0', '', '0');
INSERT INTO `dl_users` VALUES ('30', '13666689746', 'd93a5def7511da3d0f2d171d9c344e91', '2', '飞机场代理', '13666689746', '1', '1614407474', '0', '', '0');
INSERT INTO `dl_users` VALUES ('31', '13666689741', 'd93a5def7511da3d0f2d171d9c344e91', '2', '东站代理', '13666689741', '1', '1614407560', '0', '', '0');
INSERT INTO `dl_users` VALUES ('32', '13858812536', 'd93a5def7511da3d0f2d171d9c344e91', '2', '二狗子', '13858812536', '1', '1614407833', '0', '', '0');

-- ----------------------------
-- Table structure for `dl_uv_log`
-- ----------------------------
DROP TABLE IF EXISTS `dl_uv_log`;
CREATE TABLE `dl_uv_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '访问ip',
  `time` int(10) NOT NULL DEFAULT '0' COMMENT '时间',
  `date` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '访问时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dl_uv_log
-- ----------------------------
