
PHP CACHE 从ThinkPHP 5.0 中提取出来
===============



> require: php>=5.4

## 安装

使用composer安装

~~~
composer require james-xiao/think-cache

~~~

用法

~~~
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

~~~

然后就可以在命令行

~~~
// php 要加入到环境变量中
php test.php

~~~

输出 
~~~
array(2) {
  [0]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
  [1]=>
  array(1) {
    ["a"]=>
    int(3333)
  }
}

~~~


## 更多

+ [参考手册](https://www.kancloud.cn/manual/thinkphp5/118131)
