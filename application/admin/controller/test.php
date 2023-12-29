<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/22
 * Time: 13:18
 */
//
//
//$str = "123";
//$num=12344;
//var_dump(is_string($str));
//var_dump(is_int($num));
//var_dump(is_integer($num));

//
//
//$str = "123456xx";
//unset($str);
//if(!isset($str)){
//    echo 'str is not exists.';
//}else{
//    echo 'str is exists.';
//    if(empty($str)){
//        echo 'str is null.';
//    }else{
//        echo $str;
//    }
//}
//
//$var = '';
//$var2;
//$var3 = null;
//// 结果为 TRUE，所以后边的文本将被打印出来。
//if (isset($var)) {
//    echo "This var is set so I will print.";
//}
//var_dump(isset($var));
//var_dump(isset($var2));
//unset($var3);
//var_dump(isset($var3));
//var_dump($var3);
//

//$var = 1895204165301;
//var_dump($var);
//$varint = intval($var);
//var_dump($varint);
//$varstr = strval($var);
//var_dump($varstr);

//$int1 = 10;
//$int2 = 90;
//
//var_dump(__FILE__);
//var_dump(__LINE__);


//$x=5; // 全局变量
//
//function myTest()
//{
//    $y=10; // 局部变量
//    global $x;
//    echo "<p>Test variables inside the function:<p>";
//    echo '<br>';
//    echo "Variable x is: $x";
//    echo "<br>";
//    echo "Variable y is: $y";
//}
//
//myTest();
//
//echo "<p>Test variables outside the function:<p>";
//echo '<br>';
//echo "Variable x is: $x";
//echo "<br>";
//echo "Variable y is: $y";


////$str = 10;
////$str2 = 20;
//
//function myTest(){
//    //$GLOBALS['str2'] = $GLOBALS['str']+$GLOBALS['str2'];
//
//
//    static $str = 0;
//    echo $str;
//    echo '<br/>';
//    $str+=1;
//}
//
//myTest();
//myTest();
//myTest();
////var_dump($GLOBALS['str2'] );
///
///

//
//echo getcwd();
//
//chdir("test");
//
//echo getcwd();


//$dir = '../../../public/static/img';
//
//$list = scandir($dir);
//print_r( $list);
//
//$b = scandir($dir,1);
//print_r($b);
//if(is_dir($dir)){
//    if($dh = opendir($dir)){
//        echo "dh:".$dh;
//        while(($file = readdir($dh))!=false){
//            echo "filename:".$file;
//            echo '<br>';
//        }
//        closedir($dh);
//    }
//}else{
//    echo $dir;
//}


//$a = 58138 * 62659;
//$b = 49652 * 79876;
//$c = 39652 * 85876;
//
//var_dump("a:" . $a);
//var_dump("b:" . $b);
//var_dump("c:" . $c);
//
////echo $a > $b ? $a : $a > $c ? $a : $c;
//
//echo '<br>';
//
//
//$a > $b ? $x = ($a < $c ? $c : $a) : $x = ($b < $c ? $c : $b);
//
//$a > $b ? $x = ($a > $c ? $a : $c) : $x = ($b > $c ? $b : $c);
//echo $x;


//$a =4;
//$b=5;
//$c=6;
//
//$d=$a*$b+($b--)+$b%$c-$b*$c;
//Var_dump($a<$b || $c>$d);
//date_default_timezone_set("Asia/Shanghai");
//echo date("Y-m-d h:i:sa");

//
//$begintime = mktime(0,0,0,date("m"),date("d")-1,date("y"));
//echo "begintime:".date("Y-m-d H:i:s",$begintime);
//echo 'begin->end:';
//for ($i=0;$i<24;$i++){
//    $b = $begintime+($i*3600);
//    $e = $begintime+(($i+1)*3600)-1;
//    echo date("Y-m-d H:i:s",$b)."=>".date("Y-m-d H:i:s",$e);
//}

//$int1 = 8;
//$int2 = 6;
//var_dump($int1%$int2);
//var_dump($int1/$int2);


//$num = 88;
//
//if ($num >= 90) {
//    echo 'perfect!!!';
//} elseif ($num >= 80 || $num < 90) {
//    echo 'good!!!';
//} elseif ($num >= 60 || $num < 80) {
//    echo 'pass!!!';
//} else {
//    echo 'failed!!!';
//}


//$start = 1;
//$sum = 0;
//while ($start <= 50) {
////    for ($item = 0; $item < 50; $item++) {
////        if ($start % 2 == 0) {
////            $sum += $start;
////
////            echo "sum:".$sum;
////            $start++;
////        }
//    //}
//
//
//    if ($start % 2 == 0) {
//        $sum += $start;
//    }
//    $start++;
//}
//echo $sum;

//
//$start = 1;
//$sum = 0;
//for ($item = 0; $item < 50; $item++) {
//    if ($start % 2 == 0) {
//        $sum += $start;
//    }
//    $start++;
//}
//echo $sum;

//
//for($item=1;$item<10;$item++){
//    for ($j=1;$j<10;$j++){
//        echo "$item*$j=".$j*$item."  ";
//    }
//    echo PHP_EOL;
//}
//
//
//
//for ($item = 0; $item < 5; $item++) {
//    for ($j = 0; $j < 5; $j++) {
//        echo "*";
//    }
//    echo PHP_EOL;
//}
//


//for ($i = 0; $i < 10; $i++) {
//    echo $i;
//    goto a;
//    echo "string";
//}
//
//echo "<br>";
//echo "2";
//a:
//echo "<br>";
//echo "3";
//
//
//$a = 1;
//do {
//    echo $a;
//    $a++;
//} while ($a < 10);
//


///**
// * 1.求偶:求100以内的偶数   取模  %   求奇数,对偶数进行取反或者取模等于1
// *
// * 2.九九乘法表
// *
// * 3.一球从100米高度自由落下，每次落地后反跳回原高度的一半；再落下，求它在 第10次落地时，共经过多少米？第2次反弹多高？
// */
//

//
//$str = "我们都是程序员!!!!";
//$str2 = "程序员";
//var_dump(strstr($str,$str2)) ;

//
//$find = array("Hello","world");
//$replace = array("B","C");
//$arr = array("Hello","world","!");
//print_r(str_replace($find,$replace,$arr));

//$arr = array('Hello','World!','I','love','Shanghai!');
//
//echo join(" ",$arr);
//$fnum = 123.01;
//
//echo ceil($fnum);
//echo PHP_EOL;
//echo floor($fnum);
//echo PHP_EOL;
//echo round($fnum);
//$str = "Hello-world.-I-love-Shanghai!";
//print_r (explode("-",$str));

//echo number_format("5000000");
//echo PHP_EOL;
//echo number_format("5000000",2);
//echo PHP_EOL;
//echo number_format("5000000",2,",",".");
//echo PHP_EOL;
//echo number_format("5000000",2,",",",");

//echo strcmp("Hello world!","Hello world!"); // 两字符串相等
//echo PHP_EOL;
//echo strcmp("Hello world!","Hello"); // string1 大于 string2
//echo PHP_EOL;
//echo strcmp("Hello world!","Hello world! Hello!"); // string1 小于 string2
//echo PHP_EOL;
//echo strncmp("I love s","I love c",8);
//
//$sum=0;
//$height=100;
//
//settype($height,'double');
//echo gettype($height);
//echo PHP_EOL;
//echo $height;
//echo PHP_EOL;
//
//var_dump( empty($height));
//echo PHP_EOL;
//$arr1 = array("变量",1=>"数据类型",4=>"运算符","上节课学习的"=>"流程控制","数组",8=>"函数",
//    "面向对象入门",15=>"魔术方法","面向对象特性","命名空间",
//    "mysql基本操作",25=>"数据类型","列属性","高级查询","子查询","pdo"
//);
//var_dump( is_null($arr1));


//for ($i=0;$i<10;$i++){
//    $sum+= $height+$height/2;
//    $height = $height/2;
//
//    if($i==1){
//        echo "第2次反弹".$height."米";
//        echo PHP_EOL;
//    }
//}
//
//echo "第10次落地时，共经过".$sum."米";
//echo PHP_EOL;
//echo "第10次落地时，高度为".$height."米";

//
//for ($i = 0; $i < 100; $i++) {
//    if ($i % 2 == 0 && $i <> 0) {
//        echo $i;
//        echo PHP_EOL;
//    }
//
//}
//
//for($i=1;$i<=9;$i++){
//    for ($j=1;$j<=$i;$j++){
//        echo "$i*$j=".$j*$i."  ";
//    }
//    echo PHP_EOL;
//}
//

//$ht = 100;
//$sedht = 0;
//$sum=$ht;
//for ($i = 0; $i < 10; $i++) {
//    $ht = $ht / 2;
//    echo $ht." ";
//echo 'i:'.$i." ";
//echo PHP_EOL;
//
//    if ($i == 1) {
//        $sedht = $ht;
//    }
//
//    $sum+=$ht;
//}
//echo "total height:".$sum;
//echo PHP_EOL;


//$arr1 = array("变量",1=>"数据类型",4=>"运算符","上节课学习的"=>"流程控制","数组",8=>"函数",
//    "面向对象入门",15=>"魔术方法","面向对象特性","命名空间",
//    "mysql基本操作",25=>"数据类型","列属性","高级查询","子查询","pdo"
//);
//通过健打印数组$arr中的数组这个值，遍历数组按照  （健：1，值：数据类型）这个样式打印出数组。


//foreach ($arr as $key=>$value){
//    echo "key:".$key.","."value:". iconv('UTF-8','UTF-8',  $value);
//    echo PHP_EOL;
//}

//（PHP、C、C++、PYTHON、JAVA、C#）。
//$arr[]="PHP";
//$arr[]="C";
//$arr[]="C++";
//$arr[]="PYTHON";
//$arr[]="JAVA";
//$arr[]="C#";
//
//
//$arr2 = array("PHP", "C", "C++", "PYTHON", "JAVA", "C#");

//foreach ($arr2 as $key=>$value){
//    echo "key:".$key.","."value:". iconv('UTF-8','UTF-8',  $value);
//    echo PHP_EOL;
//}

//foreach ($arr2 as $value){
//    echo "value:". iconv('UTF-8','UTF-8',  $value);
//    echo PHP_EOL;
//}

//
//$playerc = array("上古的星飞","勇攀一指峰","远交近攻的弘光","吻了狗的盛康");
//$player = array("上古的星飞","勇攀一指峰","远交近攻的弘光");
//foreach ($player as $item) {
//
//if(in_array($item,$playerc)){
//    var_dump(iconv('UTF-8','UTF-8',  $item));
//
//    var_dump(iconv('UTF-8','UTF-8',  "存在于playerc数组中"));
//}
//
//    var_dump(iconv('UTF-8','UTF-8',  $item));
//    echo PHP_EOL;
//    echo iconv('UTF-8','UTF-8',  $item);
//    echo PHP_EOL;
//}

//
//
//

//
//function test($num)
//{
//    $str = "0123456789abcdefghijklmnopqrstuvwxyz";
//    $restr = "";
//    $rearr = array();
//    for ($i = 0; $i < $num; $i++) {
//        $temp = $str[rand(0, strlen($str) - 1)];
//
//        if (!in_array($temp, $rearr)) {
//            $rearr[$i] = $temp;
//        }
//    }
//    foreach ($rearr as $item) {
//        $restr .= $item;
//    }
//    return $restr;
//}
//
//print_r(test(10));

//
//function test($num,$f){
//    echo $num."$f".$num;
//}
//
//test(10,"+");


//$arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, array(10, 11, 12));

//$strvalue  = array_values($arr);
//var_dump($strvalue);
//
//$strkey = array_keys($arr);
//var_dump($strkey);


//$arrstr = ["name"=>"jack ma","age"=>56];
//$arrstr2 = array_reverse($arrstr);


//var_dump(date('w',time()));


//$config = [
//    'host' => 'localhost',
//    'username' => 'root',
//    'password' => 'root',
//    'databases' => '',
//    'port' => 3306,
//    'conding' => 'utf8'
//];
//
//$cnf = [
//    'host' => '172.17.0.2',
//    'username' => 'root',
//    'password' => 'cuiyuanxin66666',
//    'databases' => 'nndb'
//];
//
//var_dump(array_merge($config,$cnf));
//


//foreach ($arr as $key =>$item) {
//
//    if(is_array($item)){
//        foreach ($item as $k=>$v) {
//            print_r($v);
//            echo PHP_EOL;
//        }
//    }else{
//        print_r($item);
//        echo PHP_EOL;
//    }
//}


//
//function my_callback_function(){
//    echo 'this is my callback_function';
//}
//
//
// call_user_func('my_callback_function');


// An example callback method
//class MyClass {
//    static function myCallbackMethod() {
//        echo 'Hello World!';
//    }
//}
//
//call_user_func('MyClass::myCallbackMethod');


//class A{
//    public static function who(){
//        echo 'A\n';
//    }
//}
//
//class B extends A{
//    public static function who()
//    {
//        echo 'B\n';
//        //parent::who(); // TODO: Change the autogenerated stub
//    }
//}
//
//call_user_func(array('B','parent::who'));

//
//class CallableClass{
//    public function __invoke($var1,$var2)
//    {
//        var_dump($var1,$var2);
//    }
//}
//
//$obj = new CallableClass();
//$obj('Hello' ,'World!!!');
//
//
//var_dump(is_callable($obj));
//
//class Person
//
//{
//
//    public $sex;
//
//    public $name;
//
//    public $age;
//
//    public function __construct($name="",  $age=25, $sex='man')
//
//    {
//
//        $this->name = $name;
//
//        $this->age  = $age;
//
//        $this->sex  = $sex;
//
//    }
//
//    public function __invoke($name,$age,$sex) {
//
//        echo '这可是一个对象哦';
//        echo  PHP_EOL;
//
//echo $name;
//echo PHP_EOL;
//        echo $age;
//        echo PHP_EOL;
//        echo $sex;
//        echo PHP_EOL;
//    }
//
//}
//
//$person = new Person('Yao'); // 初始赋值
//$person($person->name,$person->age,$person->sex);
//
////var_dump($person());
//
//var_dump(is_callable($person));

//
//
//class Person {
//    var $name;
//    var $age;
//
//    //定义一个构造方法初始化赋值
//    function __construct($name,  $age) {
//        $this->name=$name;
//        $this->age=$age;
//    }
//
//    function say() {
//        echo "我的名字叫：".$this->name."<br />";
//        echo "我的年龄是：".$this->age;
//    }
//}
//
//$p1=new Person("张三", 20);
//$p1->say();

//
//// Our closure
//$double = function($a) {
//    return $a * 2;
//};
//
//
//function my_function($var){
//    return $var*$var;
//}
//
//// This is our range of numbers
//$numbers = range(1, 5);
////$numbers = rand(1, 5);
//print implode(' ', $numbers);
//
//print PHP_EOL;
//// Use the closure as a callback here to
//// double the size of each element in our
//// range
//$new_numbers = array_map($double, $numbers);
//$new_numbers2 = array_map('my_function', $new_numbers);
//
////
//print implode(' ', $new_numbers);
//print PHP_EOL;
//print implode(' ', $new_numbers2);

//$str = str_replace("ll", "", "good golly miss molly!", $count);
//echo $count;
//echo PHP_EOL;
//echo $str;
//echo PHP_EOL;

//good goy miss moy


//search string or array


//
//$letters = array('a', 'p');
//$fruit   = array('apple', 'pear');
//$text    = 'a p';
//$output  = str_replace($letters, $fruit, $text);
//echo $output;
//
////apearpearle pear
//
////applepear
//
//
//
//$search  = array('A', 'B', 'C', 'D', 'E');
//$replace = array('B', 'C', 'D', 'E', 'F');
//$subject = 'A';
//echo str_replace($search, $replace, $subject);
//
//
//class Person
//{
//    public $name = 'Jay Chou';
//    private $sex = 'man';
//
//    function __get($name)
//    {
//      echo "this is __get:".$this->sex;
//    }
//
//    function __set($name, $value)
//    {
//        $this->$name = $value;
//    }
//
//    function getSex()
//    {
//        echo $this->sex;
//    }
//
//    function __isset($name)
//    {
//        // TODO: Implement __isset() method.
//    }
//
//    /**
//     *
//     */
//    function __toString()
//    {
//        // TODO: Implement __toString() method.
//        return;
//    }
//}
//
//$obj = new Person();
//$obj->sex = 'female';
////echo $obj->getSex();
//echo $obj->__get('sex');


//
//$queue = array("orange", "banana");
//array_unshift($queue, "apple", "raspberry");
//print_r($queue);
//
//echo PHP_EOL;
//
////$fruit = array_shift($queue);
////print_r($fruit);
//
//
//$stack = array("orange", "banana", "apple", "raspberry");
//$fruit = array_shift($stack);
//print_r($stack);


//$stack = array("orange", "banana", "apple", "raspberry");
//$fruit = array_pop($stack);
//echo "stack array:";
//print_r($stack);
//echo PHP_EOL;
//echo "fruit array:";
//var_dump($fruit);


//
//function nuke_keys($keys, $array) {
//    return array_diff_key($array, array_fill_keys($keys, 0));
//}
//$array = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
//$keys  = array('red', 'purple');
//
//print_r(nuke_keys($keys, $array));

//
//class Hero
//{
//    public $name;
//    public $age;
//    protected $height;
//    private $money;
//
//    function __construct($name, $age, $height, $money)
//    {
//        $this->name = $name;
//        $this->age = $age;
//        $this->height = $height;
//        $this->money = $money;
//    }
//
//    function __get($name)
//    {
//        // TODO: Implement __get() method.
//        $this->name = $name;
//    }
//
//    function __set($name, $value)
//    {
//        $this->$name = $value;
//    }
//
//    function __isset($name)
//    {
//        // TODO: Implement __isset() method.
//        //$this->name
//    }
//}
//
//$hero = new Hero('猪八戒', 500, 180, 1000000000);
//$hero->name = "老猪";
//$hero->height = 220;
//$hero->money = 99999999;
//
//define('MONEY', 20000);
//
//class Bank
//{
////    //创建一个银行卡Bank类，
//////$name public，银行卡持有者姓名，
//////$card_num protected，银行卡号,
//////$password private 银行卡密码，
//////$money 静态化银行卡的钱数初始为0。
//////类的外面定义一个全局常量MONEY =  20000；Bank类里面定义一个类常量MONEY  = 1000；
////1.封装一个构造方法，初始化姓名，银行卡号（卡号要求18位，随机的四位数字+年月日时分秒），银行卡密码。
////2.定义存钱的方法，每次存入全局常量MONEY，就是说每次都是存入20000元。
////3.定义取钱的方法，每次取类常量MONEY的钱，就是说每次都是取1000元。
////4.创建Minbank类，继承Bank类，重构构造方法，初始化姓名，银行卡号（卡号要求18位，9999+年月日时分秒），银行卡密码。
////5.实例化2个类，Bank类的对象存2次钱，取3次钱，Minbank类的对象取2次钱，最后还剩多少钱
//
//    const MONEY = 1000;
//    public $name;
//    protected $card_num;
//    private $password;
//    public static $money = 0;
//
//    public function __construct($name, $card_num, $password)
//    {
//        $this->name = $name;
//        $this->card_num = $card_num;
//        $this->password = $password;
//    }
//
//    function test(){
//        echo self::$money;
//    }
//
//    function plus()
//    {
//        self::$money += MONEY;
//
//    }
//
//    function minus(){
//        self::$money -=Bank::MONEY;
//    }
//}
//
//$obj = new Bank('张三', rand(1000, 9999) . date("Ymdhsi"), '123456');
//$obj->plus();
//$obj->plus();
//$obj->minus();
//$obj->minus();
//$obj->minus();
//var_dump( $obj::$money);
//var_dump($obj);
//
//
//
//class MinBank extends Bank{
//
//}
//
//$bank = new MinBank('张三', "9999" . date("Ymdhsi"), '123456');
//
//$bank->minus();
//$bank->minus();
//
//
//print_r("最后输出的money:".$bank::$money);

//
//class cA
//
//{
//
//    /**
//     * 直接使用的测试属性的默认值
//     */
//
//    protected static $item = 'Foo';
//
//
//    /**
//     * 间接使用的测试属性的默认值
//     */
//
//    protected static $other = 'cA';
//
//
//    public static function method()
//
//    {
//
//        print self::$item . "\r\n";
//
//        print self::$other . "\r\n";
//
//    }
//
//
//    public static function setOther($val)
//
//    {
//
//        self::$other = $val;
//
//    }
//
//}
//
//
//class cC extends cA
//
//{
//
//    /**
//     * 重定义测试属性的默认值
//     */
//
//    protected static $item = 'Tango';
//
//
//    public static function method()
//
//    {
//
//        print self::$item . "\r\n";
//
//        print self::$other . "\r\n";
//
//    }
//    /**
//     * 不重新声明 setOther()方法
//     */
//}
//
//
//cC::setOther('cC'); // cA::method()!
//cC::method(); // cC::method()!


//
//trait Animal{
//    public $name = "one";
//    public static $age=1;
//
//    public function  eat(){
//        echo "eat....";
//    }
//}
//
//
//class Panda{
//    use Animal;
//}
//
//$obj = new Panda();
//echo $obj->name;
//echo PHP_EOL;
//$obj->eat();

//class Base {
//    public function sayHello() {
//        echo 'Hello ';
//    }
//}
//
//trait SayWorld {
//    public function sayHello() {
//        parent::sayHello();
//        echo 'World!';
//    }
//}
//
//class MyHelloWorld extends Base {
//    use SayWorld;
//}
//
//$o = new MyHelloWorld();
//$o->sayHello();
//
//class Student
//{
//    const NORMAL = 1;
//    const FORBIDDEN = 2;
//    /**
//     * 用户ID
//     * @var 类型
//     */
//    public $id;
//    /**
//     * 获取id
//     * @return int
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//    public function setId($id = 1)
//    {
//        $this->id = $id;
//    }
//}
//$ref = new ReflectionClass('Student');
//$doc = $ref->getDocComment();
//echo $ref->getName() . ':' . getComment($ref);
//
//echo "属性列表：<br/>";
//printf("%-15s%-10s%-40s<br/>", 'Name', 'Access', 'Comment');
//$attr = $ref->getProperties();
//foreach ($attr as $row) {
//    printf("%-15s%-10s%-40s<br/>", $row->getName(), getAccess($row), getComment($row));
//}
//echo "常量列表：<br/>";
//printf("%-15s%-10s<br/>", 'Name', 'Value');
//$const = $ref->getConstants();
//foreach ($const as $key => $val) {
//    printf("%-15s%-10s<br/>", $key, $val);
//}
//echo "<br/><br/>";
//echo "方法列表<br/>";
//printf("%-15s%-10s%-30s%-40s<br/>", 'Name', 'Access', 'Params', 'Comment');
//$methods = $ref->getMethods();
//foreach ($methods as $row) {
//    printf("%-15s%-10s%-30s%-40s<br/>", $row->getName(), getAccess($row), getParams($row), getComment($row));
//}
//
//
//
//// 获取权限
//function getAccess($method)
//{
//    if ($method->isPublic()) {
//        return 'Public';
//    }
//    if ($method->isProtected()) {
//        return 'Protected';
//    }
//    if ($method->isPrivate()) {
//        return 'Private';
//    }
//}
//// 获取方法参数信息
//function getParams($method)
//{
//    $str = '';
//    $parameters = $method->getParameters();
//    foreach ($parameters as $row) {
//        $str .= $row->getName() . ',';
//        if ($row->isDefaultValueAvailable()) {
//            $str .= "Default: {$row->getDefaultValue()}";
//        }
//    }
//    return $str ? $str : '';
//}
//// 获取注释
//function getComment($var)
//{
//    $comment = $var->getDocComment();
//    // 简单的获取了第一行的信息，这里可以自行扩展
//    preg_match('/\* (.*) *?/', $comment, $res);
//    return isset($res[1]) ? $res[1] : '';
//}
//
//$json ='{"PushTaskId": "9524xxxx","RequestId": "16A96B9A-F203-4EC5-8E43-CB92E68F4CD8"}';
//
//$data = json_decode($json,true);
//
//
//foreach ($data as $datum) {
//    var_dump($datum);
//    echo PHP_EOL;
//}

//var_dump($data);
//
//var_dump($data['PushTaskId']);


// 忽视位置偏移量之前的字符进行查找
//$newstring  =  'abcdef abcdef' ;
// $pos  =  strpos ( $newstring ,  'a' ,  1 );  // $pos = 7, 不是 0
//$pos1  =  strpos ( $newstring ,  'a' ,  0 );  // $pos = 7, 不是 0
//$pos2 = strrpos ($newstring,'a',1);
//$pos3 = strripos  ($newstring,'a',1);
// var_dump($pos);
// var_dump($pos1);
// var_dump($pos2);
// var_dump($pos3);


//$arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, array(10, 11, 12));

//$strvalue  = array_values($arr);
//var_dump($strvalue);
//
//$strkey = array_keys($arr);
//var_dump($strkey);

//
//$arr = array(
//    "111111111111111" => array("1111111100000000", "1111111222222222222"),
//    "222222222222222" => array("2222222200000000", "2222222222222222222"),
//);
//$strvalue = array_values($arr);
//var_dump("arr value" . $strvalue);
//
//echo PHP_EOL;
//var_dump($arr[0]);
//print_r ( array_keys (  $arr ));
//
//$array  = array( "color"  => array( "blue" ,  "red" ,  "green" ),
//    "size"   => array( "small" ,  "medium" ,  "large" ));
//print_r ( array_keys ( $array ));


//echo var_dump($arr[0][0]);

//foreach ($arr as $item) {
//    //echo "key:".$key." "."value:".$item;
//    if (is_array($item)) {
//        foreach ($item as $value) {
//
//
//            foreach ($value as $v) {
//                //var_dump($value);
//                echo PHP_EOL;
//                echo "child element value：";
//                var_dump($v);
//                echo PHP_EOL;
//            }
//
//        }
//    } else {
//        echo $item;
//        echo PHP_EOL;
//    }
//}


//var_dump(pow(2,32));


$url = "http://www.sina.com.cn/abc/de/fg.php?id=1";

function getExt($url)
{
    $arr = parse_url($url);

    $file = basename($arr['path']);
    $ext = explode('.', $file);
    var_dump("file:" . $file);
    echo PHP_EOL;

    $url = basename($url);
    var_dump("url2:" . $url);

    var_dump(strpos($url, '.'));
    echo PHP_EOL;
    var_dump(strpos($url, '?'));
//    foreach ($ext as $item) {
//        var_dump("ext:".$item);
//        var_dump(PHP_EOL);
//   }
    //return $ext[count($ext)-1];
}


//var_dump(getExt($url));


//getExt($url);

//
//function getImgExt($filename)
//{
//    $ext = explode('.', $filename);
//    foreach ($ext as $item) {
//        echo $item;
//        echo PHP_EOL;
//    }
//
//    echo count($ext);
//    echo PHP_EOL;
//    echo $ext[count($ext) - 1];
//    echo PHP_EOL;
//
//    $strcount = substr_count($filename, '.');
//    echo $strcount;
//    echo PHP_EOL;
//
//
//    $ret_str = substr($filename, 16, 4);
//    echo "ret_str:" . $ret_str;
//    echo PHP_EOL;
//
//    $ret_str2 = substr($filename, 17, 3);
//    echo "ret_str2:" . $ret_str2;
//    echo PHP_EOL;
//}


//echo getImgExt("dir/upload.image.jpg");
//
//
//$readcontents = fopen("https://www.sina.com.cn/", "rb");
//
//$contents = stream_get_contents($readcontents);
//
////$contents = file_get_contents("https://www.sina.com.cn/");
//
//fclose($readcontents);

//echo $contents;


//$html = '<p id="">ddddd<br /></p>';
//
//echo strip_tags($html);
//
//echo "<br />";


//$subject = array('1', 'a', '2', 'b', '3', 'A', 'B', '4');
//$pattern = array('/\d/', '/[a-z]/', '/[1a]/');
//$replace = array('A:$0', 'B:$0', 'C:$0');
//
//echo "preg_filter 返回值：\n";
//print_r(preg_filter($pattern, $replace, $subject));
//
//echo "preg_replace 返回值：\n";
//print_r(preg_replace($pattern, $replace, $subject));

//function getAttrValue($str,$tagName,$attrName){
//
//    $pattern1="/<".$tagName."(\\s+\\w+\s*=\\s*([\\'\\\"]?)([^\\'\\\"]*)(\\2))*\\s+".$attrName.
//        "\\s*=\\s*([\\'\\\"]?)([^\\'\\\"]*)(\\5)(\\s+\\w+\\s*=\\s*([\\'\\\"]?)([^\\'\\\"]*)(\\9))*\\s*>/i";
//
//
//
//    $arr=array();
//
//    $re=preg_match($pattern1,$str,$arr);
//
//
//    foreach ($arr as $item) {
//        echo $item;
//        echo PHP_EOL;
//}
//    if($re){
//echo  PHP_EOL;
//        echo"<br/>\$arr[6]={$arr[6]}";
//
//    }else{
//
//        echo"<br/>没找到。";
//
//    }
//
//}
//
//
//
//$str1="<div><span class='spanc'><test attr='ddd'></span></div>";
//
////echo getAttrValue($str1,"test","attr");//找test标签中attr属性的值,结果为ddd
//header ( 'Location: http://www.baidu.com/' );
//$org_date  ="09/21/2020";
//
//$out_date = date("d/m/Y",strtotime($org_date));
//echo $out_date;

//
//$transport  = array( 'foot' ,  'bike' ,  'car' ,  'plane' );
//$mode  =  current ( $transport );  // $mode = 'foot';
//$mode  =  next ( $transport );     // $mode = 'bike';
//$mode  =  current ( $transport );  // $mode = 'bike';
//$mode  =  prev ( $transport );     // $mode = 'foot';
//$mode  =  end ( $transport );      // $mode = 'plane';
//$mode  =  current ( $transport );  // $mode = 'plane';
//
////$arr  = array();
////var_dump ( current ( $arr ));  // bool(false)
////
////$arr  = array(array());
////var_dump ( current ( $arr ));  // array(0) { }
//
//
////var_dump(array_keys($transport));
//var_dump(array_values($transport));

//
//date_default_timezone_set('PRC');
//
//
///**
// * 获取给定月份的上一月最后一天
// * @param $date string 给定日期
// * @return string 上一月最后一天
// */
//
//function get_last_month_last_day($date = '')
//{
//
//    if ($date != '') {
//
//        $time = strtotime($date);
//
//    } else {
//
//        $time = time();
//
//    }
//
//    $day = date('j', $time);//获取该日期是当前月的第几天
//
//    return date('Y-m-d', strtotime("-{$day} days", $time));
//
//}


//
//// 测试
//
//echo get_last_month_last_day();
//
//echo "<br />";
//
//echo get_last_month_last_day("2013-3-10");

//
//$arr = array(1, 43, 54, 62, 21, 66, 32, 78, 36, 76, 39);
//
//$count = count($arr);
//
//for ($i = 0; $i < $count - 1; $i++) {
//    for ($k = 0; $k < $count - $i - 1; $k++) {
//        if ($arr[$k] > $arr[$k + 1]) {
//            $temp = $arr[$k + 1];
//            $arr[$k + 1] = $arr[$k];
//            $arr[$k] = $temp;
//        }
//    }
//}


//
//
//foreach ($arr as $item) {
//    echo $item;
//    echo PHP_EOL;
//}
//
//

//
//
//function mk($n ,$m){
//$arr = range(1,$n);//构造一个数组
//
//    foreach ($arr as $item) {
//        echo $item;
//        echo PHP_EOL;
//    }
//
//        $i = 1; //从第一个开始循环
//        while(count($arr)>1){ //如果总数大于1
//            ($i % $m != 0) && array_push($arr,$arr[$i-1]);//不被踢出则压入数组尾部
//            unset($arr[$i-1]);//压入数组然后删除
//            $i++;//继续循环
//        }
//        return $arr[$i-1]; //直至最后剩下一个为大王
//}
//print_r(mk(16,3));   //第3只为大王

////$title ="我是标题";
//     $title = iconv('UTF-8','gbk',  '悄悄是别离的笙箫');
////$title= iconv('gbk','UTF-8','悄悄是别离的笙箫');
//echo $title;


//
//$str = "abc123def";
//$patt1 = '/[0-9]+/';
//$patt2 = '/abc/';
//
////echo preg_match($patt2,$str,$matches);
//
//// 从URL中获取主机名称
//preg_match('@^(?:http://)?([^/]+)@i',
//    "http://www.runoob.com/index.html", $matches);
//echo "matches:{$matches}\n";
//foreach ($matches as $match) {
//    echo "match:{$match}\n";
//}
//$host = $matches[1];
//echo "host:{$host}\n";
//// 获取主机名称的后面两部分
//preg_match('/[^:]+\.[^.]+\.[^.]+$/', $host, $matches);
//echo "domain name is: {$matches[0]}\n";
//
//preg_match('/[^.]+\.[^.]+$/', $host, $matches);
//echo "domain name is: {$matches[0]}\n";
//
////模式分隔符后的"i"标记这是一个大小写不敏感的搜索
////if (preg_match("/[0-9]+/", "abc123def")) {
////    echo "查找到匹配的字符串 php。";
////} else {
////    echo "未发现匹配的字符串 php。";
////}

//$timestamp = 1608021162;
//if(date('y-m',$timestamp)==date('y-m',time())){
//    echo "match";
//}else{
//    echo "not match";
//}
//echo date('y-m',$timestamp);
//echo date('m',$timestamp);
//echo PHP_EOL;
//echo date('y-m',time());
//echo date('m',time());


//
//$str_time = '2020-09-11 11:54:57';
//
//echo date('H',strtotime($str_time));


//
//$i   =138;
//echo $i<<3;


$arr = array(3, 5, 8, 4, 9, 6, 1, 7, 2);

echo PHP_EOL;
$len = count($arr);
//for ($i = 0; $i < $len; $i++) {
//    for ($j = 1; $j < $len - $i; $j++) {
//        if ($arr[$j] < $arr[$j - 1]) {
//            $temp = $arr[$j - 1];
//            $arr[$j - 1] = $arr[$j];
//            $arr[$j] = $temp;
//        }
//    }
//}


function arr_sort($arr_sort)
{
    $len_sort = count($arr_sort);
    for ($i = 0; $i < $len_sort; $i++) {
        for ($j = $len_sort - 1; $j > $i; $j--) {
            if ($arr_sort[$j] < $arr_sort[$j - 1]) {
                $temp = $arr_sort[$j];
                $arr_sort[$j] = $arr_sort[$j - 1];
                $arr_sort[$j - 1] = $temp;
            }
        }
    }
    return $arr_sort;
}

//$arr_sort = array(10, 2, 36, 14, 10, 25, 23, 85, 99, 45);
//print_r(arr_sort($arr_sort));


// 斐波那契数列非递归 F0=1 F1=1 Fn=F(n-1)+F(n-2)


function fibo_series($n)
{
    $array = array();
    $array[0] = 1;
    $array[1] = 1;

    for ($f = 2; $f < $n; $f++) {
        $array[$f] = $array[$f - 1] + $array[$f - 2];
    }
    print_r($array);
}

//fibo_series(20);


function fibo_recursion($n)
{
    if ($n == 1 || $n == 2) {
        return 1;
    } else {
        return $value = fibo_recursion($n - 1) + fibo_recursion($n - 2);
    }
}

//echo PHP_EOL;
//echo 'fibo recursion';
//echo PHP_EOL;
//print_r(fibo_recursion(20));

//# 利用引用做参数
//function test($a=0,&$result=array()){
//    $a++;
//    if ($a<10) {
//        $result[]=$a;
//        test($a,$result);
//    }
//    echo $a;
//    echo PHP_EOL;
//    return $result;
//}
//
//print_r(test());
//
//
//# 利用全局变量
//function test1($a=0,$result=array()){
//    global $result;
//    $a++;
//    if ($a<10) {
//        $result[]=$a;
//        test($a,$result);
//    }
//    return $result;
//}
//
//print_r('test1 result:');
//echo  PHP_EOL;
//print_r(test1());

//# 利用静态变量
//function test()
//{
//    static $count = 0;
//    echo $count;
//    $count++;
//}
//
//test();
//test();
//test();
//test();
//test();


//function test($a=0){
//    static $result=array();
//    $a++;
//    if ($a<10) {
//        $result[]=$a;
//        test($a);
//    }
//    return $result;
//}
//
//print_r(test());


//function test($a=0){
//    $a++;
//    if ($a<10) {
//        echo $a.' ';
//        test($a);
//    }
//}
//print_r(test());


//function summation ($count) {
//    if ($count != 0) :
//        return $count + summation($count-1);
//    endif;
//}
//$sum = summation(10);
//print "Summation = $sum";

//
//function test ($n){
//    echo $n." ";
//    if($n>0){
//        test($n-1);
//    }else{
//        echo "";
//    }
//    echo $n." ";
//}
//test(2);


# 有一对兔子,从出生后第3个月起每个月都生一对兔子,小兔子长到第三个月后每个月又生一对兔子,假如兔子都不死,请编程输出两年内每个月的兔子总数为多少?
//function calRabbit($month)
//{
//    $fir_mon = 1;
//    $sec_mon = 1;
//    $sum = 0;
//    if ($month < 3) {
//        return 1;
//    }
//    for ($m = 2; $m < $month; $m++) {
//        $sum = $fir_mon + $sec_mon;
//        $fir_mon = $sec_mon;
//        $sec_mon = $sum;
//
//    }
//
//    print_r($sum);
//
//}

//calRabbit(24);


//function calRabbit_1($m)
//{
//    $f =1;
//    $s =1 ;
//    $sum = 0;
//    if($m==1||$m==2){return 1;}
//    else{
//     return calRabbit_1($m-1)+calRabbit_1($m-2);
//    }
//    //echo $m.'后兔子总数为:'.$sum;
//
//}
//
//print_r(calRabbit_1(3));

# hello
//function reverse_r($str){
//    if(strlen($str)>0){
//        echo strlen($str);
//        echo PHP_EOL;
//        echo substr($str,1);
//        echo PHP_EOL;
//
//        reverse_r(substr($str,1));
//    }
//    echo substr($str,0,1);
//    return;
//}

//function reverse_i($str){
//    for($i=1; $i<=strlen($str);$i++){
//        # i = 1-5
//        echo substr($str,-$i,1);
//        echo PHP_EOL;
//        echo substr($str,-$i,1);
//    }
//    return;
//}
//
//print_r(reverse_i("hello"));


$num = 41;
$step = 3;
function joseph($arr, $step, $start, $survivors)
{
    foreach ($arr as $k => $v) {
        if ($start % $step === 0) {
            unset($arr[$k]);
            $start = 1;
        } else {
            $start++;
        }
    }
    if (count($arr) > $survivors)
        return joseph($arr, $step, $start, $survivors);
    else
        return $arr;
}

$i = 0;
$arr = [];
//while($i ++ < $num){
//    $arr[] = $i;
//}


for ($i = 1; $i <= $num; $i++) {
    $arr[] = $i;
}

$arr = joseph($arr, 3, 1, 2);
print_r($arr);


$num = 41;
$step = 3;
/**
 * @param Array $arr
 * @param int $step
 * @param int $start 起始位置
 * @param int $survivors 存活人数
 * @return array
 */
function Josephus($arr, $step, $start, $survivors)
{
    foreach ($arr as $k => $v) {
        if ($start % $step === 0) {
            unset($arr[$k]);
            $start = 1;
        } else {
            $start++;
        }
    }
    if (count($arr) > $survivors)
        return Josephus($arr, $step, $start, $survivors);
    else
        return $arr;
}

$i = 0;
$arr = [];
while ($i++ < $num) {
    $arr[] = $i;
}
$arr = Josephus($arr, 3, 1, 2);
print_r($arr);


# 递归实现字符串翻转
function reverse_r($str)
{
    if (strlen($str) > 0) {
        reverse_r(substr($str, 1));
    }
    echo substr($str, 0, 1);
    return;
}

# 循环实现字符串翻转
function reverse_i($str)
{
    for ($i = 1; $i <= strlen($str); $i++) {
        echo substr($str, -$i, 1);
    }
    return;
}


# 二分法的使用数组必须是有序的，或升序，或降序
$arr = array(
    1, 3, 5, 7, 9, 13
);
# 递归调用
function bsearch_r($v, $arr, $low, $high)
{
    if ($low > $high) {// 先判断结束条件
        return -1;
    }
    $i = intval(($high + $low) / 2);
    if ($arr[$i] > $v) {
        return bsearch_r($v, $arr, $low, $i - 1);// 递归
    } else if ($arr[$i] < $v) {
        return bsearch_r($v, $arr, $i + 1, $high);
    } else {
        return $i;
    }
}

echo bsearch_r(1, $arr, 0, count($arr) - 1);// 0
echo '<hr/>';
echo bsearch_r(14, $arr, 0, count($arr) - 1);// -1
echo '<hr/>';


# while循环
function bsearch($v, $arr)
{
    $low = 0;
    $high = count($arr) - 1;# 使用下标，注意减去1
    # 注意凡是使用到while的时候，一定要防备无限循环的时候，注意终止循环的判断。
    while ($low <= $high) {
        # 比如$low<=$high，这个等于号必须有。
        $i = intval(($high + $low) / 2);
        if ($arr[$i] > $v) {
            $high = $i - 1;
        } else if ($arr[$i] < $v) {
            $low = $i + 1;
        } else {
            return $i;
        }
    }
    return -1;# 找不到的时候返回-1
}

echo bsearch(13, $arr);# 5
echo '<hr/>';
echo bsearch(14, $arr);# -1


function preorder($root)
{
    $stack = array();
    array_push($stack, $root);
    while (!empty($stack)) {
        $center_node = array_pop($stack);
        echo $center_node->value; // 根节点
        if ($center_node->right != null)
            array_push($stack, $center_node->right); // 压入右子树
        if ($center_node->left != null)
            array_push($stack, $center_node->left); // 压入左子树
    }
}


function inorder($root)
{
    $stack = array();
    $center_node = $root;
    while (!empty($stack) || $center_node != null) {
        while ($center_node != null) {
            array_push($stack, $center_node);
            $center_node = $center_node->left;
        }
        $center_node = array_pop($stack);
        echo $center_node->value;
        $center_node = $center_node->right;
    }
}

function tailorder($root)
{
    $stack = array();
    $outstack = array();
    array_push($$stack, $root);
    while (empty($stack)) {
        $center_node = array_pop($stack);
        array_push($outstack, $center_node);
        if ($center_node->right != null)
            array_push($stack, $center_node->right);
        if ($center_node->left != null)
            array_push($stack, $center_node->left);
    }
    while (empty($outstack)) {
        $center_node = array_pop($outstack);
        echo $center_node->value;
    }
}


function getResult($month)
{
    $one = 1; //第一个月兔子的对数
    $two = 1; //第二个月兔子的对数
    $sum = 0; //第$month个月兔子的对数
    if ($month < 3) {
        return;
    }
    for ($i = 2; $i < $month; $i++) {
        $sum = $one + $two;
        $one = $two;
        $two = $sum;
    }
    echo $month . '个月后共有' . $sum . '对兔子';
}

function fun($n)
{
    if ($n == 1 || $n == 2) {
        return 1;
    } else {
        return fun($n - 1) + fun($n - 2);
    }
}

#斐波那契数列指的是这样一个数列 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233，377，610，987，1597，2584，4181，6765，10946，17711，28657，46368........
#这个数列从第3项开始，每一项都等于前两项之和。
#F0=0，F1=1，Fn=F(n-1)+F(n-2)

function fib($n)
{
    $array = array();
    $array[0] = 1;
    $array[1] = 1;
    for ($i = 2; $i < $n; $i++) {
        $array[$i] = $array[$i - 1] + $array[$i - 2];
    }
    print_r($array);
}

function fib_recursive($n)
{
    if ($n == 1 || $n == 2) {
        return 1;
    } else {
        return fib_recursive($n - 1) + fib_recursive($n - 2);
    }
}

$arr = array(3, 5, 8, 4, 9, 6, 1, 7, 2);
#---------------------------------------
#常用排序算法
#---------------------------------------
#冒泡排序
function BubbleSort($arr)
{
    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }
    for ($i = 0; $i < $length; $i++) {
        for ($j = $length - 1; $j > $i; $j--) {
            if ($arr[$j] < $arr[$j - 1]) {
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j - 1];
                $arr[$j - 1] = $tmp;
            }
        }
    }
    return $arr;
}

echo '冒泡排序：';
echo implode(' ', BubbleSort($arr)) . "<br/>";

//快速排序
function QSort($arr)
{
    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }
    $pivot = $arr[0];//枢轴
    $left_arr = array();
    $right_arr = array();
    for ($i = 1; $i < $length; $i++) {//注意$i从1开始0是枢轴
        if ($arr[$i] <= $pivot) {
            $left_arr[] = $arr[$i];
        } else {
            $right_arr[] = $arr[$i];
        }
    }
    $left_arr = QSort($left_arr);//递归排序左半部分
    $right_arr = QSort($right_arr);//递归排序右半部份
    return array_merge($left_arr, array($pivot), $right_arr);//合并左半部分、枢轴、右半部分
}

echo "快速排序：";
echo implode(' ', QSort($arr)) . "<br/>";


//选择排序(不稳定)
function SelectSort($arr)
{
    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }
    for ($i = 0; $i < $length; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < $length; $j++) {
            if ($arr[$j] < $arr[$min]) {
                $min = $j;
            }
        }
        if ($i != $min) {
            $tmp = $arr[$i];
            $arr[$i] = $arr[$min];
            $arr[$min] = $tmp;
        }
    }
    return $arr;
}

echo "选择排序：";
echo implode(' ', SelectSort($arr)) . "<br/>";

//插入排序
function InsertSort($arr)
{
    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }
    for ($i = 1; $i < $length; $i++) {
        $x = $arr[$i];
        $j = $i - 1;
        while ($x < $arr[$j] && $j >= 0) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $x;
    }
    return $arr;
}

echo '插入排序：';
echo implode(' ', InsertSort($arr)) . "<br/>";


//二分查找
function binary_search($arr, $low, $high, $key)
{
    while ($low <= $high) {
        $mid = intval(($low + $high) / 2);
        if ($key == $arr[$mid]) {
            return $mid + 1;
        } elseif ($key < $arr[$mid]) {
            $high = $mid - 1;
        } elseif ($key > $arr[$mid]) {
            $low = $mid + 1;
        }
    }
    return -1;
}

$key = 6;
echo "二分查找{$key}的位置：";
echo binary_search(QSort($arr), 0, 8, $key);

//顺序查找
function SqSearch($arr, $key)
{
    $length = count($arr);
    for ($i = 0; $i < $length; $i++) {
        if ($key == $arr[$i]) {
            return $i + 1;
        }
    }
    return -1;
}

$key = 8;
echo "<br/>顺序常规查找{$key}的位置：";
echo SqSearch($arr, $key);




