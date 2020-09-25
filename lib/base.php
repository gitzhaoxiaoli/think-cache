<?php
define('EXT', '.php');
define('LIB_PATH', ROOT_PATH . 'lib' . DS);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);


require  'lib/Loader.php';
// 注册自动加载
\think\Loader::register();
// 注册命名空间定义
\think\Loader::addNamespace('think' , LIB_PATH );
