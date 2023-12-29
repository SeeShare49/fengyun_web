
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="__LIB__/jquery-2.0.3.min.js"></script>
</head>
<body>
    <form id="form">
        <input id="msg" value="00000000"/>
    </form>
</body>

<?php
$sendStr="client";
$socket=socket_create(AF_INET,SOCK_STREAM,getprotobyname("tcp"));

if(socket_connect($socket,"192.168.1.230",40011)){

    $receiveStr="";

    $receiveStr=socket_read($socket,1024);
    echo "client:".$receiveStr;

    socket_write($socket,$sendStr,strlen($sendStr));
echo '<script>alert($receiveStr)</script>';
}

?>
<script>
   alert( document.getElementById("msg").value);
</script>
</html>



