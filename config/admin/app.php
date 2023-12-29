<?php
return [
    /* 模板相关配置 */

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => Env::get('think_path') . 'tpl/admin_dispatch_jump.tpl',
    'dispatch_error_tmpl' => Env::get('think_path') . 'tpl/admin_dispatch_jump.tpl',

    //系统更新目录
    'UPDATE_PATH' => './application/update/',
    //'UPDATE_PATH' => __ROOT__ . 'application/update/',

    //系统更新远程地址
    'UPDATE_SERVER_URL' => 'http://www.52yiwan.com/gameweb/update.html',

    //分页配置
    'paginate' => [
        'type' => 'think\paginator\driver\Layer',
        'var_page' => 'page',
        'list_rows' => 30,
    ],

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制
        'exts' => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'rootPath' => 'uploads/picture', //保存根路径
    ),

    /* 视频上传相关配置 */
    'VIDEO_UPLOAD' => array(
        'maxSize' => 500 * 1024 * 1024, //上传的文件大小限制
        'exts' => 'mp4,ogg,webm', //允许上传的文件后缀
        'rootPath' => 'uploads/video', //保存根路径
    ),

    /* 文件上传相关配置 */
    'FILE_UPLOAD' => array(
        'maxSize' => 500 * 1024 * 1024, //上传的文件大小限制
        'exts' => 'jpg,gif,png,jpeg,txt,pdf,doc,docx,xls,xlsx,zip,rar,ppt,pptx', //允许上传的文件后缀
        'rootPath' => 'uploads/file', //保存根路径
    ),

    //用户密码加密字符串
    'UC_AUTH_KEY' => 'Kx"X![4(W+n?;OdD:/%_BF3r1w0fmGyc{8JtHQlM',
    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => 'zz_admin',
        // 驱动方式 支持redis memcache memcached
        'type' => '',
        // 是否自动开启 SESSION
        'auto_start' => true,
    ],

];
