<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace xiao;

class Loader
{
    /**
     * @var array 实例数组
     */
    protected static $instance = [];

    /**
     * @var array 类名映射
     */
    protected static $classMap = [];

    /**
     * @var array 命名空间别名
     */
    protected static $namespaceAlias = [];

    /**
     * @var array PSR-4 命名空间前缀长度映射
     */
    private static $prefixLengthsPsr4 = [];

    /**
     * @var array PSR-4 的加载目录
     */
    private static $prefixDirsPsr4 = [];

    /**
     * @var array PSR-4 加载失败的回退目录
     */
    private static $fallbackDirsPsr4 = [];

    /**
     * @var array PSR-0 命名空间前缀映射
     */
    private static $prefixesPsr0 = [];

    /**
     * @var array PSR-0 加载失败的回退目录
     */
    private static $fallbackDirsPsr0 = [];

    /**
     * @var array 需要加载的文件
     */
    private static $files = [];

    /**
     * 自动加载
     * @access public
     * @param  string $class 类名
     * @return bool
     */
    public static function autoload($class)
    {
        // 检测命名空间别名
        if (!empty(self::$namespaceAlias)) {
            $namespace = dirname($class);
            if (isset(self::$namespaceAlias[$namespace])) {
                $original = self::$namespaceAlias[$namespace] . '\\' . basename($class);
                if (class_exists($original)) {
                    return class_alias($original, $class, false);
                }
            }
        }

        if ($file = self::findFile($class)) {
            // 非 Win 环境不严格区分大小写
            if (!IS_WIN || pathinfo($file, PATHINFO_FILENAME) == pathinfo(realpath($file), PATHINFO_FILENAME)) {
                __include_file($file);
                return true;
            }
        }

        return false;
    }

    /**
     * 查找文件
     * @access private
     * @param  string $class 类名
     * @return bool|string
     */
    private static function findFile($class)
    {
        // 类库映射
        if (!empty(self::$classMap[$class])) {
            return self::$classMap[$class];
        }

        // 查找 PSR-4
        $logicalPathPsr4 = strtr($class, '\\', DS) . EXT;
        $first           = $class[0];

        if (isset(self::$prefixLengthsPsr4[$first])) {
            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
                        if (is_file($file = $dir . DS . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-4 fallback dirs
        foreach (self::$fallbackDirsPsr4 as $dir) {
            if (is_file($file = $dir . DS . $logicalPathPsr4)) {
                return $file;
            }
        }

        // 查找 PSR-0
        if (false !== $pos = strrpos($class, '\\')) {
            // namespace class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
            . strtr(substr($logicalPathPsr4, $pos + 1), '_', DS);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DS) . EXT;
        }

        if (isset(self::$prefixesPsr0[$first])) {
            foreach (self::$prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-0 fallback dirs
        foreach (self::$fallbackDirsPsr0 as $dir) {
            if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                return $file;
            }
        }

        // 找不到则设置映射为 false 并返回
        return self::$classMap[$class] = false;
    }

    /**
     * 注册 classmap
     * @access public
     * @param  string|array $class 类名
     * @param  string       $map   映射
     * @return void
     */
    public static function addClassMap($class, $map = '')
    {
        if (is_array($class)) {
            self::$classMap = array_merge(self::$classMap, $class);
        } else {
            self::$classMap[$class] = $map;
        }
    }

    /**
     * 注册命名空间
     * @access public
     * @param  string|array $namespace 命名空间
     * @param  string       $path      路径
     * @return void
     */
    public static function addNamespace($namespace, $path = '')
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $paths) {
                self::addPsr4($prefix . '\\', rtrim($paths, DS), true);
            }
        } else {
            self::addPsr4($namespace . '\\', rtrim($path, DS), true);
        }
    }

    /**
     * 添加 PSR-0 命名空间
     * @access private
     * @param  array|string $prefix  空间前缀
     * @param  array        $paths   路径
     * @param  bool         $prepend 预先设置的优先级更高
     * @return void
     */
    private static function addPsr0($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            self::$fallbackDirsPsr0 = $prepend ?
            array_merge((array) $paths, self::$fallbackDirsPsr0) :
            array_merge(self::$fallbackDirsPsr0, (array) $paths);
        } else {
            $first = $prefix[0];

            if (!isset(self::$prefixesPsr0[$first][$prefix])) {
                self::$prefixesPsr0[$first][$prefix] = (array) $paths;
            } else {
                self::$prefixesPsr0[$first][$prefix] = $prepend ?
                array_merge((array) $paths, self::$prefixesPsr0[$first][$prefix]) :
                array_merge(self::$prefixesPsr0[$first][$prefix], (array) $paths);
            }
        }
    }

    /**
     * 添加 PSR-4 空间
     * @access private
     * @param  array|string $prefix  空间前缀
     * @param  string       $paths   路径
     * @param  bool         $prepend 预先设置的优先级更高
     * @return void
     */
    private static function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            // Register directories for the root namespace.
            self::$fallbackDirsPsr4 = $prepend ?
            array_merge((array) $paths, self::$fallbackDirsPsr4) :
            array_merge(self::$fallbackDirsPsr4, (array) $paths);

        } elseif (!isset(self::$prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException(
                    "A non-empty PSR-4 prefix must end with a namespace separator."
                );
            }

            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix]                = (array) $paths;

        } else {
            self::$prefixDirsPsr4[$prefix] = $prepend ?
            // Prepend directories for an already registered namespace.
            array_merge((array) $paths, self::$prefixDirsPsr4[$prefix]) :
            // Append directories for an already registered namespace.
            array_merge(self::$prefixDirsPsr4[$prefix], (array) $paths);
        }
    }

    /**
     * 注册命名空间别名
     * @access public
     * @param  array|string $namespace 命名空间
     * @param  string       $original  源文件
     * @return void
     */
    public static function addNamespaceAlias($namespace, $original = '')
    {
        if (is_array($namespace)) {
            self::$namespaceAlias = array_merge(self::$namespaceAlias, $namespace);
        } else {
            self::$namespaceAlias[$namespace] = $original;
        }
    }

    /**
     * 注册自动加载机制
     * @access public
     * @param  callable $autoload 自动加载处理方法
     * @return void
     */
    public static function register($autoload = null)
    {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: 'xiao\\Loader::autoload', true, true);

    }


    /**
     * 字符串命名风格转换
     * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
     * @access public
     * @param  string  $name    字符串
     * @param  integer $type    转换类型
     * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param  string $module       模块名
     * @param  string $layer        层名 controller model ...
     * @param  string $name         类名
     * @param  bool   $appendSuffix 是否添加类名后缀
     * @return string
     */
    public static function parseClass($module, $layer, $name, $appendSuffix = false)
    {

        $array = explode('\\', str_replace(['/', '.'], '\\', $name));
        $class = self::parseName(array_pop($array), 1);
        $class = $class . (App::$suffix || $appendSuffix ? ucfirst($layer) : '');
        $path  = $array ? implode('\\', $array) . '\\' : '';

        return App::$namespace . '\\' .
            ($module ? $module . '\\' : '') .
            $layer . '\\' . $path . $class;
    }

    /**
     * 初始化类的实例
     * @access public
     * @return void
     */
    public static function clearInstance()
    {
        self::$instance = [];
    }
}

// 作用范围隔离

/**
 * include
 * @param  string $file 文件路径
 * @return mixed
 */
function __include_file($file)
{
    return include $file;
}

/**
 * require
 * @param  string $file 文件路径
 * @return mixed
 */
function __require_file($file)
{
    return require $file;
}
