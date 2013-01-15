<?php

namespace framework\socket;

/**
 *  connection 处理类
 */
class Connection {

    private static $conns = [];

    public static function add($uid, $conn) {
        if(self::check($uid)) {
            self::close($uid);
        }
        self::$conns[$uid] = [
            'conn'=> $conn,
            'time'=> time(),
        ];
    }

    public static function check($uid) {
        return isset(self::$conns[$uid]);
    }

    public static function boardcast($data) {
        foreach(self::$conns as $conn) {
            $conn['conn']->write($data);
        }
    }

    public static function send($uid, $data) {
        self::$conns[$uid]['conn']->write($data);
    }

    public static function close($uid) {
        self::$conns[$uid]['conn']->end();
        unset(self::$conns[$uid]);
    }

    public static function heartBeat($uid) {
        self::$conns[$uid]['time'] = time();
    }

    public static function activeCheck() {
        foreach(self::$conns as $uid=>$conn) {
            if(time() - $conn['time'] > 1200) {
                self::close($uid);
            }
        }
    }
}