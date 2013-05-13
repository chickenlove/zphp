<?php

namespace framework\helper\storage;

use framework\manager;

/**
 * redis-stroage 存储处理类
 * redis-stroage 是一个支持leveldb做为持久存储的redis增强版本
 * redis-stroage获取：https://github.com/qiye/redis-storage
 * 如要支持redis-stroage，需要使用增强版本phpredis扩展
 * 增强版本phpredis扩展地址：https://github.com/shenzhe/phpredis
 */
class RLHelper implements IStorage {

    private $redis;
    private $sRedis = null;
    private $suffix = "";
    private $pconnect = false;

    public function __construct($name, $pconnect = false) {
        if (empty($this->redis)) {
            $this->redis = manager\RedisManager::getInstance($name, $pconnect);
            $this->pconnect = $pconnect;
        }
    }

    public function setSlave($name, $pconnect = false) {
        if (empty($this->sRedis)) {
            $this->sRedis = manager\RedisManager::getInstance($name, $pconnect);
        }
    }

    public function setKeySuffix($suffix) {
        $this->suffix = $suffix;
    }

    private function uKey($userId) {
        return $userId . '_' . $this->suffix;
    }


    public function getMutilMD($userId, $keys) {
        $uKey = $this->uKey($userId);
        return $this->redis->rlHMGet($uKey, $keys);
    }

    public function getMD($userId, $key, $slaveName = "") {
        $uKey = $this->uKey($userId);
        return $this->redis->rlHGet($uKey, $key);
    }

    public function getSD($userId, $key, $slaveName = "") {
        $uKey = $this->uKey($userId);
        return $this->redis->dsHGet($uKey, $key);
    }

    public function setSD($userId, $key, $data) {
        $uKey = $this->uKey($userId);
        return $this->redis->dsHSet($uKey, $key, $data);
    }

    public function setMD($userId, $key, $data, $cas = false) {
        if ($cas) {
            return $this->setMDCAS($userId, $key, $data);
        }
        $uKey = $this->uKey($userId);
        $dsKey = $this->dsKey($uKey, $key);
        if ($this->redis->dsSet($dsKey, $data)) {
            return $this->redis->hSet($uKey, $key, $data);
        }

        return false;
    }

    public function addMD($userId, $key, $data) {
        $uKey = $this->uKey($userId);
        if ($this->redis->dsHGet($uKey, $key)) {
            throw new \Exception("{$key} exist");
        }
        if ($this->redis->dsHSet($uKey, $key, $data)) {
            return $this->redis->hSetNx($uKey, $key, $data);
        }
        return false;
    }

    public function setMDCAS($userId, $key, $data) {
        $uKey = $this->uKey($userId);
        $this->redis->watch($uKey);
        $result = $this->redis->multi()->hSet($uKey, $key, $data)->exec();
        if (false === $result) {
            throw new \Exception('cas error');
        }
        if ($this->redis->dsHSet($uKey, $key, $data)) {
            return true;
        }

        throw new \Exception('dsSet error');
    }

    public function del($userId, $key) {
        $uKey = $this->uKey($userId);
        return $this->redis->rlHDel($uKey, $key);
    }

    public function delSD($userId, $key, $slavename='') {
        $uKey = $this->uKey($userId);
        return $this->redis->dsHDel($uKey, $key);
    }

    public function setMultiMD($userId, $keys) {
        $uKey = $this->uKey($userId);
        if ($this->redis->dsHMSet($uKey, $keys)) {
            return $this->redis->hMSet($uKey, $keys);
        }

        return false;
    }

    public function close() {
        if ($this->pconnect) {
            return true;
        }

        $this->redis->close();

        if (!empty($this->sRedis)) {
            $this->sRedis->close();
        }

        return true;
    }

    public function getMulti($cmds) {
        $this->redis->multi(\Redis::PIPELINE);
        $uids = [];
        foreach ($cmds as $userId => $key) {
            $uids[] = $userId;
            $uKey = $this->uKey($userId);
            $this->redis->rlHGet($uKey, $key);
        }

        return $this->redis->exec();
    }

    public function setExpire($userId, $key, $time) {
        $uKey = $this->uKey($userId);
        return $this->redis->setTimeout($uKey, $time);
    }

}
