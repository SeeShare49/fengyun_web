<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/4
 * Time: 18:18
 */


namespace app\common;

use app\admin\controller\Gangs;
use tests\mail_plus;
use tests\web_packet_chat;
use tests\webw_packet_add_quest;
use tests\webw_packet_change_sect_master;
use tests\webw_packet_change_sect_notice;
use tests\webw_packet_config_activity;
use tests\webw_packet_create_system_sect;
use tests\webw_packet_forbidden_chat;
use tests\webw_packet_game_notice;
use tests\webw_packet_kick_off_player;
use tests\webw_packet_mail_to_all_server;
use tests\webw_packet_mail_to_one_server;
use tests\webw_packet_notice;
use tests\webw_packet_assign_kuafu;
use tests\webw_packet_purchase;
use tests\webw_packet_recharge_refund;
use tests\webw_packet_reload_table;
use think\facade\Log;
use think\facade\Response;
use think\facade\View;
use think\Session;
use function MongoDB\BSON\toJSON;


define("TEMP", "../extend/protobuf/");

define("SOCKET_IP", config('admin.SOCKET_SERVER_IP'));
define("SOCKET_PORT", config('admin.SOCKET_SERVER_PORT'));
require_once TEMP . "library/DrSlump/Protobuf.php";

\DrSlump\Protobuf::autoload();

require_once TEMP . "tests/protos/Recharge.php";
require_once TEMP . "tests/protos/Mail.php";
require_once TEMP . "tests/protos/mail_plus.php";
require_once TEMP . "tests/protos/web_ban_user.php";
require_once TEMP . "tests/protos/web_add_money.php";
require_once TEMP . "tests/protos/web_add_item.php";
require_once TEMP . "tests/protos/modify_server_name.php";
require_once TEMP . "tests/protos/server_list.php";
require_once TEMP . "tests/protos/Game.php";
require_once TEMP . "tests/protos/shut_down_server.php";
require_once TEMP . 'tests/protos/webw_packet_notice.php';
require_once TEMP . 'tests/protos/webw_packet_game_notice.php';
require_once TEMP . 'tests/protos/web_packet_chat.php';
require_once TEMP . 'tests/protos/webw_packet_forbidden_chat.php';
require_once TEMP . 'tests/protos/webw_packet_add_quest.php';
require_once TEMP . 'tests/protos/webw_packet_create_system_sect.php';
require_once TEMP . 'tests/protos/webw_packet_change_sect_master.php';
require_once TEMP . 'tests/protos/webw_packet_change_sect_notice.php';
require_once TEMP . 'tests/protos/webw_packet_assign_kuafu.php';
require_once TEMP . 'tests/protos/webw_packet_recharge_refund.php';
require_once TEMP . 'tests/protos/webw_packet_kick_off_player.php';
require_once TEMP . 'tests/protos/webw_packet_reload_table.php';
require_once TEMP . 'tests/protos/webw_packet_purchase.php';

class test
{
    const IP = SOCKET_IP;
    const PORT = SOCKET_PORT;


    /**
     * code:275
     * 自动开服
     * 服务器消息变更发送消息
     */
    static function change_server_config()
    {
        self::combin_pack(self::IP, self::PORT, '', 275);
    }

    /**
     * code:276
     * 创建游戏服务器
     * 新开服或合服发送消息
     * （弃用）
     */
    static function create_or_combine_server()
    {
        self::combin_pack(self::IP, self::PORT, '', 276);
    }

    /**
     * code:800
     * 添加道具
     *
     * @param $server_id  服务器ID
     * @param $play_name  玩家昵称
     * @param $item_id    道具数量
     * @param $item_count 是否绑定
     * @param $bind
     */
    static function webw_packet_add_item($server_id, $play_name, $item_id, $item_count, $bind)
    {
        $obj = new \tests\web_add_item();
        $obj->setGameId($server_id);
        $obj->setPlayerName($play_name);
        $obj->setItemId($item_id);
        $obj->setItemCount($item_count);
        $obj->setBind($bind);
        $code = 800;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:801
     * 添加金币（元宝）
     *
     * @param $server_id    服务器ID
     * @param $player_name  服务器ID
     * @param $yuan_bao     元宝数量
     * @param $gold         金币数量
     * @param $diamonds     钻石数量
     */
    static function webw_packet_add_money($server_id, $player_name, $yuan_bao, $gold, $diamonds)
    {
        $obj = new \tests\web_add_money();
        $obj->setGameId($server_id);
        $obj->setPlayerName($player_name);
        $obj->setYuanBao($yuan_bao);
        $obj->setGold($gold);
        $obj->setDiamonds($diamonds);
        $code = 801;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:802
     * 充值
     *
     * @param $server_id       服务器ID
     * @param $recharge_id     充值ID
     */
    static function webw_packet_recharge($server_id, $recharge_id)
    {
        $obj = new \tests\Recharge();
        $obj->setGameId($server_id);
        $obj->setRechargeId($recharge_id);
        $code = 802;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:824
     * 直充（直购）
     **/
    static function webw_packet_purchase($server_id, $recharge_id)
    {
        $obj = new webw_packet_purchase();
        $obj->setGameId($server_id);
        $obj->setRechargeId($recharge_id);
        $code = 824;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:803
     * 用户封停
     *
     * @param $user_id  玩家ID
     * @param $is_ban   是否封停
     * @param $reason   封停缘由
     */
    static function webw_packet_ban_user($user_id, $is_ban, $reason)
    {
        $obj = new \tests\web_ban_user();
        $obj->setUserId($user_id);
        $obj->setBan($is_ban);
        $obj->setReason($reason);
        //$code = 803;
        $code = 701;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:804
     * 邮件发送(指定某个玩家)
     *
     * @param $server_id    服务器ID
     * @param $name         玩家昵称
     * @param $title        邮件标题
     * @param $content      邮件内容
     * @param $prop_info    道具ID:道具数量 或数组
     */
    //static function mail($server_id, $name, $title, $content, $item_id, $item_count)
    static function mail($server_id, $name, $title, $content, $prop_info)
    {
        $obj = new \tests\Mail();
        $obj->setGameId($server_id);
        $obj->setPlayerName($name);
        $obj->setTitle($title);
        $obj->setContent($content);
        //$obj->setPropInfo($prop_info);
        $obj->setPropInfoList($prop_info);
        $code = 804;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:815
     * 单服或者多服邮件发送
     * @param $server_id
     * @param $title
     * @param $content
     * @param $prop_info
     */
    static function mail_plus($server_id, $title, $content, $prop_info)
    {
        $obj = new \tests\mail_plus();
        $obj->setGameId($server_id);
        $obj->setTitle($title);
        $obj->setContent($content);
        //$obj->setPropInfo($prop_info);
        $obj->setPropInfoList($prop_info);
        $code = 815;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }


    /**
     * 备用
     * Code:8**
     * 邮件发送（指定某个服务器）
     * @param $server_id    服务器ID
     * @param $title        邮件标题
     * @param $content      邮件内容
     * @param $items        道具信息（格式：道具ID|道具数量）
     */
    static function mail_to_one_server($server_id, $title, $content, $items)
    {
        $obj = new webw_packet_mail_to_one_server();
        $obj->setGameId($server_id);
        $obj->setTitle($title);
        $obj->setContent($content);
        $obj->setItems($items);
        $code = 8000;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * 备用
     * Code:812
     * 邮件发送（全区服）
     * @param $title
     * @param $content
     * @param $items
     */
    static function mail_to_all_server($title, $content, $items)
    {
        $obj = new webw_packet_mail_to_all_server();
        $obj->setTitle($title);
        $obj->setContent($content);
        $obj->setItems($items);
        $code = 812;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:805
     * 修改服务器名称
     *
     * @param $server_id    服务器ID
     * @param $server_name  服务器名称
     */
    static function modify_server_name($server_id, $server_name)
    {
        $obj = new \tests\modify_server_name();
        $obj->setGameId($server_id);
        $obj->setNewName($server_name);
        $code = 805;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }


    /**
     * code:807
     * 返回服务器列表
     *
     * @param $server_id    服务器ID
     * @param $server_name  服务器名称
     */
    static function web_packet_server_list($server_id, $server_name)
    {
        $obj = new \tests\server_list();
        /* $serverInfo = new \tests\server_list\server_info_node();
        $serverInfo->setGameId($server_id);
        $serverInfo->setServerName($server_name);
        $obj->setServerInfo($serverInfo); */
        $obj->setServerInfoList(array($server_id=>$server_name));
        $code = 807;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:808
     * 关闭服务器
     *
     * @param $server_id    服务器ID
     */
    static function webw_packet_shut_down_server($server_id)
    {
        $obj = new \tests\shut_down_server();
        $obj->setGameId($server_id);
        $code = 808;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:809
     * 活动配置
     * @param $server_id
     * @param $op_code
     * @param $activity_id
     * @param $func_name
     */
    static function webw_packet_config_activity($server_id, $op_code, $activity_id, $func_name)
    {
        $obj = new webw_packet_config_activity();
        $obj->setGameId($server_id);
        $obj->setOpCode($op_code);
        $obj->setActivityId($activity_id);
        $obj->setFuncName($func_name);
        $code = 809;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }


    /**
     * code:810
     * 公告发送
     * @param $server_id 服务器ID（字符串数组）
     * @param $content   公告内容
     */
    static function webw_packet_notice($server_id, $content)
    {
        $obj = new webw_packet_notice();
        $obj->setGameId($server_id);
        $obj->setContent($content);
        $code = 810;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code：811
     * 接收聊天消息
     * @param $type
     * @param $msg
     * @param $server_id
     */
    static function webw_packet_chat($type, $msg, $server_id)
    {
        $obj = new web_packet_chat();
        $obj->setType($type);
        $obj->setMsg($msg);
        $obj->setServerId($server_id);
        $code = 811;
        self::combin_pack(self::IP, 40011, $obj, $code);
    }


    /**
     * Code:812
     * 禁言操作
     * @param $server_id
     * @param $nickname
     * @param $ban
     */
    static function webw_packet_forbidden_chat($server_id, $nickname, $ban)
    {
        $obj = new webw_packet_forbidden_chat();
        $obj->setGameId($server_id);
        $obj->setNickName($nickname);
        $obj->setBan($ban);
        $code = 812;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * Code:813
     * 服务器列表信息修改刷新操作
     */
    static function refresh_server_info()
    {
        $code = 813;
        self::combin_pack(self::IP, self::PORT, '', $code);
    }


    /**
     * Code:814
     * 添加任务
     * @param $server_id  服务器ID
     * @param $nickname   角色昵称
     * @param $quest_id   任务ID
     */
    static function webw_packet_add_quest($server_id, $nickname, $quest_id)
    {
        $obj = new webw_packet_add_quest();
        $obj->setGameId($server_id);
        $obj->setNickName($nickname);
        $obj->setQuestId($quest_id);
        $code = 814;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * Code:816
     * 创建帮派信息
     * @param $server_id    服务器ID
     * @param $gangs_count  帮派数量（一次最多创建5个帮派）
     */
    static function webw_packet_create_system_sect($server_id, $gangs_count)
    {
        $obj = new webw_packet_create_system_sect();
        $obj->setGameId($server_id);
        $obj->setCount($gangs_count);
        $code = 816;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * Code:817
     * 帮派会长转移
     * @param $server_id
     * @param $sect_id
     * @param $actor_id
     */
    static function webw_packet_change_sect_master($server_id, $sect_id, $actor_id)
    {
        $obj = new webw_packet_change_sect_master();
        $obj->setGameId($server_id);
        $obj->setSectId($sect_id);
        $obj->setActorId($actor_id);
        $code = 817;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * Code:818
     * 帮派公告修改
     * @param $server_id
     * @param $sect_id
     * @param $notice
     */
    static function webw_packet_change_sect_notice($server_id, $sect_id, $notice)
    {
        $obj = new webw_packet_change_sect_notice();
        $obj->setGameId($server_id);
        $obj->setSectId($sect_id);
        $obj->setNotice($notice);
        $code = 818;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code：819
     * 定时公告发送
     * @param $server_id
     */
    static function webw_packet_game_notice($server_id)
    {
        $obj = new webw_packet_game_notice();
        $obj->setGameId($server_id);
        $code = 819;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:820
     * 服务器对应跨服标志
     * @param $kua_fu_id    跨服ID
     * @param $server_ids   服务器ID组（一个或多个，如多个需用","隔开）
     */
    static function webw_packet_assign_kuafu($kua_fu_id, $server_ids)
    {
        $obj = new webw_packet_assign_kuafu();
        $obj->setKuaFuId($kua_fu_id);
        $obj->setGameId($server_ids);
        $code = 820;
        self::combin_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:821
     * 充值退款
     * @param $id
     * @param $server_id
     * @param $actor_id
     */
    static function webw_packet_recharge_refund($id, $server_id, $actor_id)
    {
        $obj = new webw_packet_recharge_refund();
        $obj->setId($id);
        $obj->setGameId($server_id);
        $obj->setActorId($actor_id);
        $code = 821;
        self::combine_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:822
     * 踢除用户
     * @param $game_id
     * @param $user_id
     */
    static function webw_packet_kick_off_player($user_id, $game_id)
    {
        $obj = new webw_packet_kick_off_player();
        $obj->setUserId($user_id);
        $obj->setGameId($game_id);
        $code = 822;
        self::combine_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:823
     * 手动加载策划CSV数据表
     * @param $server_ids
     */
    static function webw_packet_reload_table($server_ids)
    {
        $obj = new webw_packet_reload_table();
        $obj->setGameId($server_ids);
        $code = 823;
        self::combine_pack(self::IP, self::PORT, $obj, $code);
    }

    /**
     * code:263
     * 用户登录
     * @param $data
     * @param $ip
     */
    static function contents($ip, $data)
    {
        Log::write("客户端登录请求发送！！！！");
        $obj = new \tests\Contents();
        $obj->setContens($data);
        $code = 263;
        $port = 9070;
        self::combine_pack($ip, $port, $obj, $code);
    }


    /**
     * 组合发送的数据包
     *
     * @param $ip   IP地址
     * @param $port 端口号
     * @param $obj  数据对象
     * @param $code 命令码
     * @param $sendType 发送类型 1 (长度 协议 内容[protobuf]) 0 默认 json( array( 'op_code'=>编号, 't_data'=>base64(serialize(protobuf('内容'))) ) )
     */
    static function combin_pack($ip, $port, $obj, $code, $sendType = 0)
    {
        //以前旧的使用方法 长度 协议 内容[protobuf]
        if($sendType == 1)
        {
            $first_data = pack("s*", $code);
            $first_len = 4;
            if (!empty($obj))
            {
                $second_data = $obj->serialize();//序列化
                $second_len = strlen($second_data);
                $totallen = $first_len + $second_len;
                $totallen_data = pack("s*", $totallen);
                $second_pack = $second_data;//长度 协议 内容（protobuf）
                var_dump( $totallen_data . $first_data . $second_pack);
                $pack = $totallen_data . $first_data . $second_pack;
            }
            else
            {
                $totallen = $first_len;
                $totallen_data = pack("s*", $totallen);
                $pack = $totallen_data . $first_data;
            }
        }
        //json( array( 'op_code'=>编号, 't_data'=>base64(serialize(protobuf('内容'))) ) )
        else
        {
            $second_data =  base64_encode(!empty($obj) ? $obj->serialize() : '');
            $pack = json_encode(array('op_code'=>$code,'t_data'=>$second_data));
        }
        //print_r($obj);
        self::send_msg($ip, $port, $pack);
    }

    /**
     * 客户端网关登录转发包
     * @param $ip
     * @param $port
     * @param $obj
     * @param $code
     */
    static function combine_pack($ip, $port, $obj, $code, $sendType = 0)
    {
        //以前旧的使用方法 长度 协议 内容[protobuf]
        if($sendType == 1)
        {
            $first_data = pack("s*", $code);
            $first_len = 4;
            count(array($obj));
            if (is_array($obj)) {
                \think\facade\Log::write("当前obj为数组");
            } else {
                \think\facade\Log::write("非数组");
            }
            $second_data = $obj->serialize();//序列化
            // $second_data=  serialize($obj);
            $second_len = strlen($second_data);
            $totallen = $first_len + $second_len;
            $totallen_data = pack("s*", $totallen);
            $second_pack = $second_data;//长度 协议 内容（protobuf）
            $pack = $totallen_data . $first_data . $second_pack;
        }
        //json( array( 'op_code'=>编号, 't_data'=>base64(serialize(protobuf('内容'))) ) )
        else
        {
            $second_data =  base64_encode(!empty($obj) ? $obj->serialize() : '');
            $pack = json_encode(array('op_code'=>$code,'t_data'=>$second_data));
        }
        print_r($obj);
        self::send_receive_msg($ip, $port, $pack);
        //self::send_msg($ip, $port, $pack);
    }

    /**
     * 组装消息头信息模板
     * @param string $ip ip
     * @param int $port 端口
     * @param string $content 发送的文本内容
     * @param string $method 发送模式 发送模式
     * @param string $sendPath 发送路径
     * @param string $content_type 发送的内容类型
     * @return string
     **/
    static function GetHeaders($ip, $port, $content='',$method='POST', $sendPath='/',$content_type='application/json')
    {
        $header = '';
        $header .= "{$method} {$sendPath} HTTP/1.1\r\n";
        //$header .= "Date: ".gmdate('D, d M Y H:i:s T')."\r\n";
        $header .= "User-Agent: Manage-Agent\r\n";
        $header .= "Content-Type: {$content_type}\r\n";
        $header .= "Accept: */*\r\n";
        $header .= "Host: {$ip}:{$port}\r\n";
        $header .= "Accept-Encoding: gzip, deflate, br\r\n";
        $header .= "Connection: keep-alive\r\n";
        $header .= "Content-Length: ".strlen($content)."\r\n\r\n";//必须2个\r\n表示头部信息结束
        $header .= $content;
        return $header;
    }
    
    
    /**
     * 发送消息
     *
     * @param $ip   ip地址
     * @param $port 端口号
     * @param $pack 数据包
     */
    static function send_msg($ip, $port, $pack, $isAddHttpHead = true)
    {
        //增加http头
        if($isAddHttpHead)
        {
            $pack =self::GetHeaders($ip, $port, $pack);
        }
        echo $pack;
        exit; 
        //发送消息
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        Log::write("发送socket:" . $socket);
        if ($socket < 0)
        {
            Log::write("socket_create() failed: reason: " . socket_strerror($socket) . "\n");
        }
        $result = @socket_connect($socket, $ip, $port);
        if ($result < 0)
        {
            Log::write("socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n");
        }
        else
        {
            Log::write("ip:'$ip',port:'$port'连接成功!!!");
        }
        //发送
        if (!@socket_write($socket, $pack, strlen($pack))) 
        {
            Log::Write("socket_write() failed: reason: " . @socket_strerror($socket) . "\n");
        } 
        else 
        {
            Log::write("发送到服务器信息成功！\n发送的内容为:'$pack'");
        }
        sleep(1);
        //echo "end";
        // 从客户端获取得的数据
        //        while ($out = socket_read($socket, 8192)) {
        //            Log::write("接收socket:" . $socket);
        //            Log::write("接收到的内容:" . $out);
        //
        //            echo $out."\n";
        //
        //            dump($out . '\n');
        //            var_dump($out . '\n');
        //
        //            return redirect('/gate/game_login/index',$out);
        //        }
        @socket_close($socket);
    }

    /**
     * @param $ip       发送请求IP
     * @param $port     发送请求端口
     * @param $pack     发送请求包
     */
    static function send_receive_msg($ip, $port, $pack)
    {
        //增加http头
        if($isAddHttpHead)
        {
            $pack =self::GetHeaders($ip, $port, $pack);
        }
        /* echo $pack;
        exit; */
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        Log::write("发送socket:" . $socket);
        if ($socket < 0) 
        {
            Log::write("socket_create() failed: reason: " . socket_strerror($socket) . "\n");
        }
        $result = @socket_connect($socket, $ip, $port);
        if ($result < 0)
        {
            Log::write("socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n");
        } 
        else 
        {
            Log::write("ip:'$ip',port:'$port'连接成功!!!");
        }

        if (!@socket_write($socket, $pack, strlen($pack)))
        {
            Log::Write("socket_write() failed: reason: " . @socket_strerror($socket) . "\n");
        }
        else
        {
            Log::write("发送到服务器信息成功！\n发送的内容为:'$pack'");
        }
        sleep(1);
        // 从客户端获取得的数据
        while ($out = socket_read($socket, 8192)) 
        {
            Log::write("out length:" . strlen($out));
            Log::write("socket 返回结果：" . $out);
            echo $out;
            // return redirect('/gate/GameLogin/receive?outstr=' . $out, $out);
        }
        @socket_close($socket);
    }
}