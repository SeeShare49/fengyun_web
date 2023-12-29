<?php

namespace app\admin\controller;

use Elasticsearch\ClientBuilder;
use think\Controller;

require '../vendor/autoload.php';

class ES extends Controller
{
    public function index()
    {
//        $hosts = [    'localhost:9200'    ];//本地使用localhost  也可指定IP

        $hosts=['localhost:9200'];
        $client = ClientBuilder::create()->setHosts($hosts)->build();

        $params = [
            'index'  => 'accounts',
            'type'   => 'person',
            'id'     => 1,
            'client' => [ 'ignore' => 404 ],
            'body' => ['_source' => 'abc']
        ];
        print_r ($client->get($params));
    }
}
