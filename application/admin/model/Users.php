<?php

namespace app\admin\model;

use think\Model;

class Users extends Model
{
    /**
     * 通过用户名查找用户信息
     * @param $username
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserByUserName($username)
    {
        $where[] = ['username', '=', $username];
        $where[] = ['status', '>', -1];
        return self::where($where)->find();
    }

    public static function getUserNameById($id)
    {
        return self::where('id', '=', $id)
            ->value('username');
    }


    /**
     * @param $value
     * @return string
     */
//    public function getStatusAttr($value)
//    {
//        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
//        return $status[$value];
//    }
}
