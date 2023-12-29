<?php

namespace app\admin\controller;

use Think\Db;

class Databases extends Base
{
    /**
     * 数据库备份/还原列表
     * @param null $type
     * @return mixed
     */
    public function index($type = null)
    {
        switch ($type) {
            /* 数据还原 */
            case 'import':
                //列出备份文件列表
                $path = config('DATA_BACKUP_PATH');
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }
                $path = realpath($path);
                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($path, $flag);

                $list = array();
                foreach ($glob as $name => $file) {
                    if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if (isset($list["{$date} {$time}"])) {
                            $info = $list["{$date} {$time}"];
                            $info['part'] = max($info['part'], $part);
                            $info['size'] = $info['size'] + $file->getSize();
                        } else {
                            $info['part'] = $part;
                            $info['size'] = $file->getSize();
                        }
                        $extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                        $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                        $info['time'] = strtotime("{$date} {$time}");

                        $list["{$date} {$time}"] = $info;
                    }
                }
                $title = '数据还原';
                break;

            /* 数据备份 */
            case 'export':
                $list = Db::query('SHOW TABLE STATUS');
                $list = array_map('array_change_key_case', $list);
                $title = '数据备份';
                break;
            default:
                $this->error('参数错误！');
        }

        /** @var TYPE_NAME $title */
        /** @var TYPE_NAME $list */
        $this->assign([
            'meta_title'=>$title,
            'list'=>$list
        ]);
        //渲染模板
        return $this->fetch($type);
    }

    /**
     * 优化表
     * @param  String $ids 表名
     */
    public function optimize($ids = null)
    {
        if ($ids) {
            if (is_array($ids)) {
                $ids = implode('`,`', $ids);
                $list = Db::query("OPTIMIZE TABLE `{$ids}`");

                if ($list) {
                    //添加行为记录
                    action_log("databases_optimdize", "", 0, UID);
                    $this->success("数据表优化完成！");
                } else {
                    $this->error("数据表优化出错请重试！");
                }
            } else {
                $list = Db::query("OPTIMIZE TABLE `{$ids}`");
                if ($list) {
                    //添加行为记录
                    action_log("databases_optimdize", "", 0, UID);
                    $this->success("数据表'{$ids}'优化完成！");
                } else {
                    $this->error("数据表'{$ids}'优化出错请重试！");
                }
            }
        } else {
            $this->error("请指定要优化的表！");
        }
    }
}
