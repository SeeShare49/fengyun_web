<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/10
 * Time: 16:54
 */

namespace app\common\taglib;

use think\template\TagLib;

class YwTag extends TagLib
{
    /**
     * 列表分页
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagList($tag, $content)
    {
        /** @var TYPE_NAME $orderby */
        $orderby = isset($tag['orderby']) ? $tag['orderby'] : 'sort asc,create_time desc';
        /** @var TYPE_NAME $pagesize */
        $pagesize = isset($tag['pagesize']) ? $tag['pagesize'] : 15;
        $type = isset($tag['type']) ? $tag['type'] : 'find';
        /** @var TYPE_NAME $typeid */
        $typeid = isset($tag['typeid']) ? $tag['typeid'] : '$cid';
        $void = isset($tag['void']) ? $tag['void'] : 'field';
        $model = isset($tag['model']) ? $tag['model'] : 'article';
        $where = isset($tag['where']) ? $tag['where'] : '';

        $display = isset($tag['display']) ? $tag['display'] : 1;
        $display = $display == 1 ? 1 : 0;

        $parse = '<?php ';
        $parse .= '$__FUN__ =' . "tpl_get_list(\"$orderby\",$pagesize,$typeid,\"$type\",\"$model\",\"$where\",$display);";
        $parse .= '$__LIST__ =$__FUN__["lists"];$pager = $__FUN__["model"]->render();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $void . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

}