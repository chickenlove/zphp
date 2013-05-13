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

require(__DIR__ . DS . "core/Context.php");

set_exception_handler("exception_handler");

date_default_timezone_set('Asia/Shanghai');

function __autoload($class) {
    $baseClasspath = str_replace('\\', DS, $class) . '.php';
    if(is_file(Context::getRootPath(). DS. $baseClasspath)) {  //框架文件
        $classpath = Context::getRootPath(). DS. $baseClasspath;
    }elseif(is_file(Context::getClassesRoot(). DS. $baseClasspath)){  //classes文件
        $classpath = Context::getClassesRoot() . DS . $baseClasspath;
    } else {    //第三方库文件
        $classpath = Context::getRootPath(). DS. 'lib' . DS. $baseClasspath;
    }
    require($classpath);
}

function exception_handler($exception) {
    $exceptionView = new JSONView(Formater::formatException($exception));
    $exceptionView->display();
}
