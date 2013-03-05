<?php

namespace common;

use framework\config;
use framework\manager;
use framework\helper;
use framework\view;

class Utils {

    public static $loginUserId = null;


    public static function mergePath() {
        return \implode(\DIRECTORY_SEPARATOR, \func_get_args());
    }

    public static function initConfig() {
        self::$startTime = self::getMicroTime();
        // 设置异常回调
        \set_exception_handler('common\GameException::exceptionHandler');

        if (\defined('SMARTY_LIB_PATH')) {
            // 初始化Smarty配置
            $smartyConfig = new config\SmartyConfiguration(
                            self::mergePath(ROOT_PATH, SMARTY_LIB_PATH) . DIRECTORY_SEPARATOR,
                            self::mergePath(ROOT_PATH, SMARTY_CACHE_PATH) . DIRECTORY_SEPARATOR,
                            self::mergePath(ROOT_PATH, SMARTY_COMPILE_PATH) . DIRECTORY_SEPARATOR,
                            self::mergePath(ROOT_PATH, SMARTY_TEMPLATE_PATH) . DIRECTORY_SEPARATOR,
                            self::mergePath(ROOT_PATH, SMARTY_CONFIG_PATH) . DIRECTORY_SEPARATOR
            );
            view\SmartyView::setConfiguration($smartyConfig);
        }

        manager\RedisManager::addConfigration(\CACHE_PX, new config\RedisConfiguration(\CACHE_HOST, \CACHE_PORT));

        $storageMasterHost = \explode(',', \STORAGE_MASTER_HOST);
        $storageMasterPort = \explode(',', \STORAGE_MASTER_PORT);
        $storageSlaveHost = \explode(',', \STORAGE_SLAVE_HOST);
        $storageSlavePort = \explode(',', \STORAGE_SLAVE_PORT);
        if ('Redis' === \STORAGE) {
            for ($i = 0; $i < \STORAGE_POOL_SIZE; $i++) {
                $storageMConfig = new config\RedisConfiguration($storageMasterHost[$i], $storageMasterPort[$i]);
                manager\RedisManager::addConfigration(\STORAGE_MASTER_PREFIX . $i, $storageMConfig);
                $storageSConfig = new config\RedisConfiguration($storageSlaveHost[$i], $storageSlavePort[$i]);
                manager\RedisManager::addConfigration(\STORAGE_SLAVE_PREFIX . $i, $storageSConfig);
            }
        }
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    public static function getClientIP() {

        if (isset($_SERVER) && isset($_SERVER["REMOTE_ADDR"])) {
            $realip = $_SERVER["REMOTE_ADDR"];
        } else {
            $realip = \getenv("REMOTE_ADDR");
        }
        return \addslashes($realip);
    }

}
