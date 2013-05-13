<?php

namespace framework\socket;
use framework\manager;

/**
 *  fd 容器
 */
class Connection {
    private static $redis;
    public static function getRedis() {
        if(empty(self::$redis)) {
            self::$redis = manager\RedisManager::getInstance(SOCKET_STORAGE, true);
        }
    }

    public static function addFd($fd, $uid=0) {
        self::getRedis();
        echo "addFd start ====\n";
        $ret = self::$redis->set(self::getKey($fd, 'fu'), $uid);
        var_dump($uid);
        echo "addFd end ====\n";
        return $ret;
    }


    public static function getUid($fd) {
        self::getRedis();
        return self::$redis->get(self::getKey($fd, 'fu'));
    }

    public static function add($uid, $fd) {
        self::getRedis();
        $uinfo = self::get($uid);
        if(!empty($uinfo)) {
            self::close($uid);
        }
        $data = [
            'fd'=> $fd,
            'time'=> time(),
            'types'=> ['ALL'=>1]
        ];

        self::$redis->set(self::getKey($uid), \json_encode($data));
        self::$redis->hSet(self::getKey('ALL'), $uid, $fd);
    }

    public static function addType($uid, $type) {
        self::getRedis();
        $uinfo = self::get($uid);
        $uinfo['types'][$type] = 1;
        if(self::$redis->hSet(self::getKey($type), $uid, $uinfo['fd'])) {
            self::$redis->set(self::getKey($uid), json_encode($uinfo));
        }
    }

    public static function getType($type='ALL') {
        self::getRedis();
        return self::$redis->hGetAll(self::getKey($type));
    }

    public static function get($uid) {
        self::getRedis();
        $data = self::$redis->get(self::getKey($uid));
        if(empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public static function close($fd, $uid=null) {
        return self::delete($fd, $uid);
    }

    public static function uphb($uid) {
        self::getRedis();
        $uinfo = self::get($uid);
        if(empty($uinfo)) {
            return false;
        }
        $uinfo['time'] = time();
        return self::$redis->set(self::getKey($uid), json_encode($uinfo));
    }

    public static function heartbeat($uid, $ntime = 60) {
        self::getRedis();
        $uinfo = self::get($uid);
        if(empty($uinfo)) {
            return false;
        }
        $time = time();
        if($time - $uinfo['time'] > $ntime) {
            self::delete($uinfo['fd'], $uid);
            return false;
        }
        return true;
    }

    public static function delete($fd, $uid=null, $old=true) {
        self::getRedis();
        if(null === $uid) {
            $uid = self::getUid($fd);
        }
        if($old) {
            self::$redis->delete(self::getKey($fd, 'fu'));
        }
        if(empty($uid)) {
            return ;
        }
        $uinfo = self::get($uid);
        if(!empty($uinfo)) {
            self::$redis->delete(self::getKey($uid));
            foreach($uinfo['types'] as $type=>$val) {
                self::$redis->hDel(self::getKey($type), $uid);
            }
        }
    }

    private static function getKey($uid, $prefix='uf') {
        return "{$prefix}_{$uid}_".PROJECT_NAME;
    }

    public static function clear() {
        self::getRedis();
        self::$redis->flushAll();
    }
}
