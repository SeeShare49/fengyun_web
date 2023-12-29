<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 16:04
 */

/**
 * PacketBase class
 *
 * 用以处理与c++服务端交互的sockets 包
 *
 * 注意：不支持宽字符
 *
 * @author
 *
 */

$selfPath = dirname(__FILE__);
require_once($selfPath . "/ContentHandler.php");

class PacketBase extends ContentHandler
{
    private $head;
    private $params;
    private $opcode;

    /**************************construct***************************/
    function __construct()
    {
        $num = func_num_args();
        $args = func_get_args();
        switch ($num) {
            case 0:
                //do nothing 用来生成对象的
                break;
            case 1:
                $this->__call('__construct1', $args);
                break;
            case 2:
                $this->__call('__construct2', $args);
                break;
            default:
                throw new Exception();
        }
    }

    //无参数
    public function __construct1($OPCODE)
    {
        $this->opcode = $OPCODE;
        $this->params = 0;
    }

    //有参数
    public function __construct2($OPCODE, $PARAMS)
    {
        $this->opcode = $OPCODE;
        $this->params = $PARAMS;
    }

    //析构
    function __destruct()
    {
        unset($this->head);
        unset($this->buf);
    }

    //打包
    public function pack()
    {
        $head = $this->MakeHead($this->opcode, $this->params);
        return $head . $this->buf;
    }

    //解包
    public function unpack($packet, $noHead = false)
    {
        $this->buf = $packet;
        if (!$noHead) {
            $recvHead = unpack("S2hd/I2pa", $packet);
            $SD = $recvHead[hd1];//SD
            $this->contentlen = $recvHead[hd2];//content len
            $this->opcode = $recvHead[pa1];//opcode
            $this->params = $recvHead[pa2];//params

            $this->pos = 12;//去除包头长度

            if ($SD != 21316) {
                return false;
            }
        } else {
            $this->pos = 0;
        }
        return true;
    }

    public function GetOP()
    {
        if ($this->buf) {
            return $this->opcode;
        }
        return 0;
    }
    /************************private**************************
     * 构造包头
     * @param $opcode
     * @param $param
     * @return false|string
     */
    private function MakeHead($opcode, $param)
    {
        return pack("SSII", "SD", $this->TellPut(), $opcode, $param);
    }

    //用以模拟函数重载
    public function __call($name, $arg)
    {
        return call_user_func_array(array($this, $name), $arg);
    }


    /***********************Uitl**************************
     * 将16进制的op转成10进制
     * @param $MAJOR_OP
     * @param $MINOR_OP
     * @return int
     */
    static function MakeOpcode($MAJOR_OP, $MINOR_OP)
    {
        return ((($MAJOR_OP & 0xffff) << 16) | ($MINOR_OP & 0xffff));
    }
}