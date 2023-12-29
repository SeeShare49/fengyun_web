<?php

namespace app\admin\controller;

use app\admin\model\Suggest as SuggestModel;
use think\facade\View;

class Suggest extends Base
{
    public function index()
    {
        $content = trim(input('content'));
        $where[] = ['status', '>', -1];
        if ($content) {
            $where[] = ['content', 'like', "%$content%"];
        }

        $lists = SuggestModel::where($where)->order('id desc')->paginate(config('LIST_ROWS'), false, ['query' => request()->param()]);
        $this->ifPageNoData($lists);
        $page = $lists->render();
        $this->assign([
            'content' => $content,
            'lists' => $lists,
            'page' => $page,
            'empty'=>'<td class="empty" colspan="11">暂无数据</td>',
            'meta_title' => '意见建议问题反馈'
        ]);
        return $this->fetch();
    }

    public function edit($id)
    {
        return View::fetch();
    }
}
