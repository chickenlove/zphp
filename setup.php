<?php
/**
 * 框架入口文件，类自动载入实现
 * 默认的classroot  为 classes (可自定义)
 * 命名空间和目录高度统一，会自动按命令空间载入classroot下相关的类文件
 * 如 namespace 为 Test 的 test类，对应的类文件目录为 classes/Test/test.php
 */

use framework\core\Context;
use framework\util\Formater;
use framework\view\JSONView;

if(!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
set_exception_handler("exception_handler");

date_default_timezone_set('Asia/Shanghai');
spl_autoload_register(function($class){
    static $path = null;
    if($path==null) {
        $path = dirname(dirname(__DIR__));
    }
    $baseClasspath = str_replace('\\', DS, $class) . '.php';
    if(is_file($path. DS. $baseClasspath)) {  //框架文件
        $classpath = $path. DS. $baseClasspath;
    }elseif(is_file($path. DS. 'classes'. DS . $baseClasspath)){  //classes文件
        $classpath = $path . DS . 'classes'. DS . $baseClasspath;
    } else {    //第三方库文件
        $classpath = $path. DS. 'lib' . DS. $baseClasspath;
    }
    if(is_file($classpath)) {
        require($classpath);
    }
});
function exception_handler($exception) {
    $exceptionView = new JSONView(Formater::formatException($exception));
    $exceptionView->display();
}
