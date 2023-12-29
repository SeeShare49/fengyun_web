<?php
//
//
//function mysort($arr)
//{
//    for($i = 0; $i < count($arr); $i++)
//    {
//        $isSort = false;
//        for ($j=0; $j< count($arr) - $i - 1; $j++)
//        {
//            if($arr[$j] < $arr[$j+1])
//            {
//                $isSort = true;
//                $temp = $arr[$j];
//                $arr[$j] = $arr[$j+1];
//                $arr[$j+1] = $temp ;
//            }
//        }
//        if($isSort)
//        {
//            break;
//        }
//    }
//    return $arr;
//}
//
//$arr = array(3,1,2);
//var_dump(mysort($arr));

//
//function getExt($url)
//{
//    $arr = parse_url($url);//parse_url解析一个 URL 并返回一个关联数组，包含在 URL 中出现的各种组成部分
//    //'scheme' => string 'http' (length=4)
//    //'host' => string 'www.sina.com.cn' (length=15)
//    //'path' => string '/abc/de/fg.php' (length=14)
//    //'query' => string 'id=1' (length=4)
//    $file = basename($arr['path']);// basename函数返回路径中的文件名部分
//    $ext = explode('.', $file);
//    return $ext[count($ext)-1];
//}
//
//print(getExt('http://www.sina.com.cn/abc/de/fg.html.php?id=1'));

//function fib_recursive($n)
//{
//
//    if ($n == 1 || $n == 2) {
//        return 1;
//    } else {
//        return fib_recursive($n - 1) + fib_recursive($n - 2);
//    }
//}
//print_r('start time:'.date('Y-m-d H:i:s'));
//echo PHP_EOL;
//print_r(fib_recursive(36));
//echo PHP_EOL;
//print_r('end time:'.date('Y-m-d H:i:s'));


echo \think\facade\Env::get('app_path');