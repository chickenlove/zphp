<?php

namespace framework\socket;

/**
 * socket处理工厂
 *
 */
class Factory{

    private static $cache = array();

    public static function getInstance($config, $type = "Swoole") {
        $cacheType = '\\framework\\socket\\adapter\\' . $type;
        if (!isset(self::$cache[$type])) {
            self::$cache[$type] = new $cacheType($config);
        }
        return self::$cache[$type];
    }

}
