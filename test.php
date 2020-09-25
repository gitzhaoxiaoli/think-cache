<?php

require_once __DIR__ . "/vendor/autoload.php";

$option = [
    // 驱动方式 可选 File , Redis , Memcache 等
    'type'   => 'File',
    // 缓存保存目录 type 为File时，缓存所在路径
    'path'   => './cache/',
    // 缓存前缀
    'prefix' => '',
    // 缓存有效期 0表示永久缓存
    'expire' => 0,
];

$cache = \xiao\Cache::connect($option);
//
// $cache->set('aaa', [[1, 2, 3], ['a' => 3333]]);
var_dump($cache->get('aaa'));
