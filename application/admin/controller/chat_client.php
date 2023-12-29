<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//接收套接流的最大超时时间1秒，后面是微秒单位超时时间，设置为零，表示不管它
socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
//发送套接流的最大超时时间为6秒
//socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, array("sec" => 6, "usec" => 0));

//连接服务端的套接流，这一步就是使客户端与服务器端的套接流建立联系
if (socket_connect($socket, SOCKET_IP, 40011) == false) {
    echo 'connect fail massege:' . socket_strerror(socket_last_error());
} else {
    while (true) {
        if ($buff = socket_read($socket, 8192)) {
            echo $buff . PHP_EOL;
//            file_put_contents('chat/receive.html', json_encode($buff), FILE_APPEND);
            file_put_contents( 'chat_file.txt', $buff . PHP_EOL, FILE_APPEND);
        }
    }
}
//socket_close($socket);//工作完毕，关闭套接流


function Send($newClinet, $msg)
{
    $response = 'HTTP/1.1 200 OK\r\n';
    $response .= "Content-Type:text/html;charset=UTF-8\r\n";
    $response .= "Connection:keep-alive\r\n";
    $response .= "Content-length:" . strlen($msg) . "\r\n\r\n";
    $response .= $msg;
    $ret = fwrite($newClinet, $response);
    echo $ret;
    echo "receive msg:" . $msg;
    //return redirect('chat/index?data='.$response);

}

function frame($s)
{
    $a = str_split($s, 125);
    if (count($a) == 1) {
        return "\x81" . chr(strlen($a[0])) . $a[0];
    }
    $ns = "";
    foreach ($a as $o) {
        $ns .= "\x81" . chr(strlen($o)) . $o;
    }
//    return redirect('chat/index?buff'.$ns);
    return $ns;
}
