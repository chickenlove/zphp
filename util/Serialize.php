<?php

namespace framework\util;

/**
 * 格式转换工具类
 */
class Serialize {

    public static function Serialize($data, $type = 'serialize') {
        if(empty($type)) {
            if(\function_exists('msgpack_pack')) {
                $type = 'msgpack';
            } elseif(\function_exists('igbinary_serialize')) {
                $type = 'igbinary';
            }
        }
        switch ($type) {
            case 'igbinary';
                return \igbinary_serialize($data);
                break;
            case 'msgpack':
                return \msgpack_pack($data);
                break;
            case 'json':
                return \json_encode($data);
                break;
            default:
                return \serialize($data);
        }
    }

    public static function Unserialize($data, $type = "serialize") {
        if(empty($type)) {
            if(\function_exists('msgpack_pack')) {
                $type = 'msgpack';
            } elseif(\function_exists('igbinary_serialize')) {
                $type = 'igbinary';
            }
        }
        switch ($type) {
            case 'igbinary';
                return \igbinary_unserialize($data);
                break;
            case 'msgpack':
                return \msgpack_unpack($data);
                break;
            case 'json':
                return \json_decode($data, true);
                break;
            default:
                return \unserialize($data);
        }
    }

}
