<?php

namespace app\admin\controller;

use think\Db;

use app\admin\controller\Base;

use app\admin\model;
use think\facade\Log;

class Index extends Base
{
    //后台首页
    public function index()
    {
        $brushPropWarnCount = model\BrushPropWarn::whereTime('create_time','-2 day')->count();
        $member = model\Users::find(UID);
        $this->assign([
            'member'=>$member,
            'propCount'=>$brushPropWarnCount,
        ]);
        return $this->fetch();
    }

    /**
     * 控制中心
     */
    public function main()
    {
        $where[] = ['status', '=', 1];
        //已添加文章
//        $articleCount = db('document')->count();
        $this->assign('articleCount', 0);

        //已添加文章分类
//        $categoryCount = db('document_category')->where($where)->count();
        $this->assign('categoryCount', 0);
        //后台管理员
        $memberCount = model\Users::where($where)->count();
        $this->assign('memberCount', $memberCount);

        //开服数量
        $serverCount =model\ServerList::where('status', 1)->count();
        $this->assign('serverCount', $serverCount);

        //行为日志
        $actionlogCount = model\ActionLog::where($where)->count();
        $this->assign('actionlogCount', $actionlogCount);

        if (config("WEB_TONGJI") == 1) {

            //获取今日pv
            $pvList = model\PvLog::where('date', date('Y-m-d'))->field('time,view')->order('time asc')->select();
            $this->assign('pvList', $pvList);
            //获取今日uv
            $uvList = model\UvLog::where('date', date('Y-m-d'))->field('count(id) as people,time')->group('time')->order('time asc')->select();
            $this->assign('uvList', $uvList);

            //安排最近一周的日期
            $dateArr = [];
            for ($i = 7; $i > 0; $i--) {
                array_push($dateArr, date("m-d", strtotime("-$i day")));
            }
            $this->assign('dateArr', $dateArr);

            //统计最近一周pv
            $pv7List = model\PvLog::field('sum(view) as view,date')->group('date')->order('date asc')->select();
            $this->assign('pv7List', $pv7List);

            //统计最近一周pv
            $uv7List = model\UvLog::field('count(id) as view,date')->group('date')->order('date asc')->select();
            $this->assign('uv7List', $uv7List);

            //获取TOP10被浏览页面
            $totalPv = model\UrlLog::sum('pv');
            $top10 = model\UrlLog::field('url,title,sum(pv) as pv')->order('pv desc')->limit(10)->group('url')->select();
            $this->assign('totalPv', $totalPv);
            $this->assign('top10', $top10);
        }

        return $this->fetch();
    }
}
