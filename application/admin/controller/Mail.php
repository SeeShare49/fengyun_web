<?php

namespace app\admin\controller;

use app\admin\model\ActivityType as TypeModel;
use app\admin\model\PropCsv;
use app\admin\validate\Mail as MailValidate;
use think\Db;
use Session;
use app\common;
use app\common\ServerManage;
use think\facade\Log;
use think\facade\Request;
use think\facade\View;

class Mail extends Base
{
    /**
     * 邮件列表
     * @return mixed
     */
    public function index()
    {
        /** @var TYPE_NAME $serverlist */
        $serverlist = ServerManage::getServerList();
        $title = trim(input('title'));
        $where = [];
        if ($title) {
            $where[] = ['title', 'like', "%$title%"];
        }


        $where[] = ['mail_type', '=', 1];
        $server_id = trim(input('server_id'));

        if ($server_id) {
            $where[] = ['server_id', '=', $server_id];
        }

        $actor_id = trim(input('actor_id'));

        if ($actor_id) {
            $where[] = ['actor_id', '=', $actor_id];
        }
        $user_name = trim(input('user_name'));
        if ($user_name) {
            $where[] = ['nick_name', '=', $user_name];
        }

        $lists = \app\admin\model\Mail::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();

        $this->assign([
            'lists' => $lists,
            'empty' => '<td class="empty" colspan="13">暂无数据</td>',
            'page' => $page,
            'meta_title' => '个人邮件列表',
            'actor_id' => $actor_id,
            'user_name' => $user_name,
            'server_id' => $server_id,
            'server_list' => $serverlist,
            'title' => $title
        ]);
        return $this->fetch();
    }

    /**
     * 邮件发送（指定玩家）
     */
    public function person()
    {
        if (request()->isPost()) {
            $data = $_POST;

            if ($data['server_id'] == "0") 
            {
                $this->error("请选择对应服务器ID");
            }

            $mailValidate = new MailValidate();
            if (!$mailValidate->check($data)) 
            {
                $this->error($mailValidate->getError());
            }

            $info = dbConfig($data['server_id']) ->table('player')->where('nickname', '=', trim($data['nick_name']))->find();
            if (!$info)
            {
                $this->error("昵称:【{$data['nick_name']}】玩家不存在,请核实用户昵称!");
            }


            $data['actor_id'] = $info['actor_id'];
            $data['create_time'] = $_SERVER['REQUEST_TIME'];// time();
            $data['mail_type'] = 1;

            $status = 1;
            $itemList = array();//设置物品数组列表
            $is_attach = true;//是否纯文字邮件，无附件（无道具信息）
            $prop_alias_info = '';//道具列表别名
            if (isset($data['prop_info']) && !empty($data['prop_info'])) 
            {
                $is_attach = false;
                $status = 0;//审核状态（未审核）
                $attach_arr = explode(';', trim($data['prop_info'], ' ;\t\n\r\0\x0B'));
                $attach_arr_count = count($attach_arr);
                for ($i = 0; $i < $attach_arr_count; $i++) 
                {
                    $attach_info = explode('|', $attach_arr[$i]);
                    if (count($attach_info) > 1)
                    {
                        $itemName = db('','db_table_config')->table('ItemDef')->where('Id', '=', $attach_info[0])->value('Name');
                        //$itemName = PropCsv::where('type_id', '=', $attach_info[0])->value('type_name');
                        if(!$itemName)
                        {
                            $this->error("道具:【{$attach_arr[$i]}】不存在!");
                        }
                        //设置名称组合
                        $prop_alias_info .= $itemName. '|' . $attach_info[1] . ';';
                        //设置物品
                        $itemId = intval($attach_info[0]);
                        $itemCount = intval($attach_info[1]);
                        if($itemId && $itemId)
                        {
                            $itemList[$i] = array('item_id'=>$itemId, 'item_count'=>$itemCount);
                        }
                        else
                        {
                            $this->error("道具:【{$attach_arr[$i]}】设置数量为0或不正常,请核实!");
                        }
                    }
                    else
                    {
                        $this->error("道具:【{$attach_arr[$i]}】设置不正常,请核实!");
                    }
                }
                if(!$itemList)
                {
                    $this->error("道具内容设置数据不正常,请核实!");
                }
            }
            if ($is_attach == true)
            {
                $data['send_time'] = $_SERVER['REQUEST_TIME'];
            }
            $data['status'] = $status;
            $data['prop_alias_info'] = $prop_alias_info;
            $ret = \app\admin\model\Mail::insertGetId($data);
            if ($ret)
            {
                if ($status == 1)
                {
                    //发送消息
                    common\test::mail($data['server_id'], $data['nick_name'], $data['title'], $data['content'], $itemList);
                }
                action_log('mail_person_add', 'mail', $ret, UID);
                $this->success("个人邮件信息添加成功!", 'mail/index?type=person');
                //$this->success("个人邮件信息添加成功!", '');
            } 
            else
            {
                $this->error("个人邮件信息添加失败!");
            }

            //TODO:提交数据信息由服务端处理
//            $prop_info = "";
//            if (isset($data['item_id']) && isset($data['item_count']) && (intval($data['item_id']) > 0 && intval($data['item_count']) > 0)) {
//                $prop_info .= $data['item_id'] . '|' . $data['item_count'];
//            }
//            if (isset($data['item_id_1']) && isset($data['item_count_1']) && (intval($data['item_id_1']) > 0 && intval($data['item_count_1']) > 0)) {
//                $prop_info .= ';' . $data['item_id_1'] . '|' . $data['item_count_1'];
//            }
//            if (isset($data['item_id_2']) && isset($data['item_count_2']) && (intval($data['item_id_2']) > 0 && intval($data['item_count_2']) > 0)) {
//                $prop_info .= ';' . $data['item_id_2'] . '|' . $data['item_count_2'];
//            }
//            if (isset($data['item_id_3']) && isset($data['item_count_3']) && (intval($data['item_id_3']) > 0 && intval($data['item_count_3']) > 0)) {
//                $prop_info .= ';' . $data['item_id_3'] . '|' . $data['item_count_3'];
//            }

//            common\test::mail($data['server_id'], $data['nick_name'], $data['title'], $data['content'], $data['prop_info']);
//            $this->result($data, 1, '个人邮件发送成功,待服务器处理......');
        } 
        else 
        {
            $server_list = ServerManage::getServerList();
            $server_id = trim(input('server_id'));
            // $prop_list = PropCsv::select();
            $type_id = trim(input('item_id'));

            $this->assign([
                'item_id' => $type_id,
                'server_list' => $server_list,
                'server_id' => $server_id,
                //'prop_list' => $prop_list,
                'meta_title' => '个人邮件发送'
            ]);

            return $this->fetch();
        }
    }


    /**
     * 删除指定邮件
     */
    public function delete()
    {
        $ids = input('ids/a');

        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        $where[] = ['id', 'in', $ids];
        if (\app\admin\model\Mail::where($where)->delete(true)) {
            //添加行为记录
            action_log("mail_del", "mail", $ids, UID);
            $this->success('邮件删除成功!');
        } else {
            $this->error('邮件删除失败！');
        }
    }


    /**
     * 判断数据是否为空
     * @param null $var 要判断的值
     * @param bool $zeroIsEmpty 0是否也判断为空：true-判断为空（默认），false-判断不为空
     * @return bool
     * @author Lycan ly@lyite.com
     * @date 2018-09-11
     *
     */
    function is_empty($var = null, $zeroIsEmpty = true)
    {
        // 判断数据类型
        switch (gettype($var)) {
            case 'integer':
                return $zeroIsEmpty
                    ? (0 == $var ? true : false)             // ‘0’认为是空
                    : (0 != $var && !$var ? true : false);   // ‘0’不认为是空
                break;
            case 'string':
                return (0 == strlen($var)) ? true : false;
                break;
            case 'array':
                return (0 == count($var)) ? true : false;
                break;
            case 'boolean':
                return $var ? false : true;
                break;
            default:
                return true;
                break;
        }
    }

    /**
     * 操作行为明细
     * @param $id
     * @return mixed
     * @throws \think\Exception
     */
    public function action_detail($id)
    {
        $info = Db::connect('db_config_game')->table('player_mail')->find($id);
        if ($info) {
            $this->assign([
                'info' => $info,
                'id' => $id
            ]);
        }
        return $this->fetch();
    }

    /**
     * 邮件审核
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function review($id)
    {
        $info = \app\admin\model\Mail::find($id);
        if ($info)
        {
            $data['id'] = $id;
            $data['status'] = 1;
            $data['send_time'] = time();
            $data['operator'] = USERNAME;
            if (\app\admin\model\Mail::update($data))
            {
                action_log('mail_review', "mail", $id, UID);
                //个人邮件命令发送
                if ($info['mail_type'] == 1) 
                {
                    common\test::mail($info['server_id'], $info['nick_name'], $info['title'], $info['content'], $info['prop_info']);
                }
                //单服邮件命令发送
                elseif ($info['mail_type'] == 2) 
                {
                    //common\test::mail($info['server_id'], '', $info['title'], $info['content'], $info['prop_info']);
                    common\test::mail_plus($info['server_id'], $info['title'], $info['content'], $info['prop_info']);
                } 
                //全服邮件命令发送
                else 
                {
                    common\test::mail(-1, '', $info['title'], $info['content'], $info['prop_info']);
                }
                $this->success('邮件审核成功!');
            } 
            else
            {
                $this->error('邮件审核失败!');
            }
        }
    }
}
