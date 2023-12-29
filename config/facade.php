<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/11
 * Time: 18:00
 */

return [
    'facade' => [
        \app\common\facade\ServerManageFacade::class => \app\common\ServerManage::class,
        \app\common\facade\DbManageFacade::class => \app\common\DbManage::class,
       // \app\common\facade\UnzipFacade::class => \app\common\Unzip::class,
        \app\common\facade\TypeManageFacade::class => \app\common\TypeManage::class,
        \app\common\facade\CsvManageFacade::class => \app\common\CsvManage::class,
    ],

    'alias' => [
        'ServerManage' => \app\common\facade\ServerManageFacade::class,
        'DbManage' => \app\common\facade\DbManageFacade::class,
        //'Unzip' => \app\common\facade\UnzipFacade::class,
        'TypeManage' => \app\common\facade\TypeManageFacade::class,
        'Csv' => \app\common\facade\CsvManageFacade::class,
    ],
];