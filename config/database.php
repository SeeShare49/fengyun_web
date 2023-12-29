<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'db_config_game' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_game',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],
    'db_config_main' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_main',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],
    'db_config_main_read' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_main',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],
    'db_config_log' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_log',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],
    'db_config_log_read' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_log',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],

    //聊天消息数据库连接消息
    'db_chat_log' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'cq_chat_log',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        'break_reconnection' => true,
    ],

    //新渠道（渠道ID等于或大于100）数据库连接消息
    'db_new_game_data' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '121.40.166.20',
        // 数据库名
        'database' => 'sgame_data',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'yinhe123',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [
            \PDO::ATTR_PERSISTENT => true,
//            \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
        ],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => 'yw_',
        'break_reconnection' => true,
    ],
    // 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'hostname' => '121.40.166.20',
    // 数据库名
    'database' => 'game_data',
    // 用户名
    'username' => 'root',
    // 密码
    'password' => 'yinhe123',
    // 端口
    'hostport' => '3306',
    // 连接dsn
    'dsn' => '',
    // 数据库连接参数
    'params' => [
        \PDO::ATTR_PERSISTENT => true,
//        \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
    ],
    // 数据库编码默认采用utf8
    'charset' => 'utf8',
    // 数据库表前缀
    'prefix' => 'yw_',
    // 数据库调试模式
    'debug' => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy' => 0,

    // 数据库读写是否分离 主从式有效
    'rw_separate' => false,
    // 读写分离后 主服务器数量
    'master_num' => 1,
    // 指定从服务器序号
    'slave_no' => '',
    // 自动读取主库数据
    'read_master' => false,
    // 是否严格检查字段是否存在
    'fields_strict' => true,
    // 数据集返回类型
    'resultset_type' => 'array',
    // 自动写入时间戳字段
    'auto_timestamp' => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain' => false,
    // Builder类
    'builder' => '',
    // Query类
    'query' => '\\think\\db\\Query',
    // 是否需要断线重连
    'break_reconnect' => true,
    // 断线标识字符串
    'break_match_str' => [],
];
