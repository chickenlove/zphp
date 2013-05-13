<?php

namespace framework\helper;

use framework\manager;

/**
 * 队列处理工厂
 *
 */
class QueueHelper {

    private static $cache = array();

    public static function getInstance($type = "Redis", $name = "queue", $pconnect) {
        $cacheType = '\\framework\\helper\\queue\\' . $type . 'Helper';
        if (!isset(self::$cache[$type . $name])) {
            self::$cache[$type . $name] = new $cacheType($name, $pconnect);
        }
        return self::$cache[$type . $name];
    }

}
