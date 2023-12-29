<?php

namespace app\admin\controller;

use think\facade\View;

class Gift extends Base
{
    public function index()
    {
        return View::fetch();
    }

    public function create()
    {
        return View::fetch();
    }
}
