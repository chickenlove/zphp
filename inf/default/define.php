<?php
    //端口相关
	define('PORT', 8123);
	define('HOST', "0.0.0.0");

    //====cache相关=====
    define('LOCALE_CACHE_TYPE', 'Apc');
    define('NET_CACHE_TYPE', 'Redis');
    define('CACHE_PCONNECT', true);
    define('CACHE_PX', 'rc');
    define('CACHE_HOST', '127.0.0.1');
    define('CACHE_PORT', 16379);

    //======系统相关======
    define('DEFAULT_LOCALE', 'zh_CN');
    define('DEFAULT_CHARSET', 'UTF8');
    define('PROJECT_NAME', 'chat');
    define('STATIC_SERVER', '/static/');
    define('LOCKER_PREFIX', 'CHAT_');
    define('LOCKER_TERM', 5);
    define('KEY_SEPARATOR', '.');
    define('REDIS_CONNECT_TIMEOUT', 3);
    define('SERIALIZE_TYPE', 'msgpack');

    //=======存储相关 ========
    define('STORAGE', 'Redis');
    define('LSTORAGE', 'LRedis');
    define('STORAGE_MASTER_PREFIX', 's_master');
    define('STORAGE_SLAVE_PREFIX', 's_slave');
    define('STORAGE_POOL_SIZE', 1);
    define('STORAGE_MASTER_HOST', '127.0.0.1');
    define('STORAGE_MASTER_PORT', '6379');
    define('STORAGE_SLAVE_HOST', '127.0.0.1');
    define('STORAGE_SLAVE_PORT', '6379');

    define('SMARTY_LIB_PATH', '/lib/smarty');
    define('SMARTY_CACHE_PATH', '/template/cache');
    define('SMARTY_COMPILE_PATH', '/template/compile');
    define('SMARTY_TEMPLATE_PATH', '/template/template');
    define('SMARTY_CONFIG_PATH', '/template/config');
