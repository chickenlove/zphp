zphp
====

@author: shenzhe (泽泽，半桶水)

@email: shenzhe163@gmail.com

zphp是一个极轻的的，专用于游戏(社交，网页，移动)的服务器端开发框架， 核心只提供类自动载入，路由功能，你甚至感知不到框架的存在，基本上直接写一个一个模块就行了。

根据游戏的特性，框架集成以下功能：

    存储       (ttserver, redis, redis-storage)，
    cache      (apc, memcache, redis, xcache), 
    db         (mysql)，
    队列       (beanstalk, redis)，
    排行榜     (redis)，
    socket     (tcp， react, swoole),
    daemon     (cli模式下，加 -d 即可)

要求：php5.3+， 建议使用php5.4+  (如果使用react做为socket，  必需php5.4+)


socket推荐使用swoole:
========================
https://github.com/matyhtf/php_swoole

其它方案需要扩展：http://pecl.php.net/package/libevent

增加 fcgi 模块
===================================================
socket层可直接通过fastcgi调用php, 解耦网关和业务逻辑

    $fcgiClient = new \framework\fcgi\Client('127.0.0.1', '9000');
        $response = $fcgiClient->request(
            ['query'=>http_build_query([
                'k1'=>'v1',
                'k2'=>'v2'
            ])]
        );



     
聊天室demo:
=============
    https://github.com/shenzhe/zchat




推荐的框架目录结构
==================
    bin
        --shell.php     //cli模式
        --socket.php    //socket服务

    classes       //业务逻辑
        -- common       //一些公共函数庘
        -- ctrl         //ctrl层
            IndexCtrl.php
        -- service      //业务逻辑层
        -- entity       //实体层
        -- dao          //数据层

    framework //框架目录

    lib       //第三方目录
    
    inf        //配置目录
        --default  //默认配置目录
            define.php

    template    //模版目录
          
    webroot //网站根目录
        --static
            --images
            --js
            --css
        index.php
        rpc.php

index.php代码示例：

    <?php
    use common\Utils;
    use framework\core\Context;
    use framework\dispatcher\HTTPRequestDispatcher;
    $rootPath = realpath('..');
    require ($rootPath . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "setup.php");
    Context::setRootPath($rootPath);
    $infPath = Context::getRootPath() . DS . 'inf' . DS . 'default';
    Context::setInfoPath($infPath);
    Context::initialize();  //加载inf相关目录下所有文件
    new HTTPRequestDispatcher()->dispatch();

IndexCtrl.php代码示例：

    <?php
    namespace ctrl;
    use framework\DataView;
    class IndexCtrl {
        public function index() {
            echo 'hello world';
        }

        public function json() {
            return DataView::getView(
                array("a"=>"b","c"=>123)
            );
        }

        /*
         *  添加 template/index/html.php文件,内容如下
         *  <?php
         *      echo $a.$c;
         */

        public function html() {
            return DataView::getView(
                array("a"=>"b","c"=>123),
                'html',
                'index/html.php'
            );
        }
    }

输入 http://host/?act=Index.index http://host/?act=Index.json  http://host/?act=Index.html 访问 
