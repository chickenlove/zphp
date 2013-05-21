<?php

namespace framework\socket;

/**
 * connect处理工厂
 *
 */
class CFactory{

    private static $cache = array();

    public static function getInstance($obj, $type = "Redis") {
        $cacheType = '\\framework\\socket\\conn\\' . $type;
        if (!isset(self::$cache[$type])) {
            self::$cache[$type] = new $cacheType($obj);
        }
        return self::$cache[$type];
    }

}
