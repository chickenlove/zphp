<?php

namespace dao;

use framework\helper,
    framework\util;

abstract class UserDaoBase extends DaoBase {

    protected $storage = null;
    public $cacheHelper = null;
    private static $readCache = array();
    private static $writeCache = array();
    private static $addCache = array();
    private static $casCache = array();

    const INFO_INDEX = 0;

    private $poolSize = 0;

    const AUTO_ID_KEY = 'AUTOID';
    const PUBLIC_AUTOID_KEY = 'PUBLIC_ID';

    public function __construct($entity, $noCache = true) {
        $this->entity = $entity;
        if(!$noCache) {
            $this->useCache();
        }
    }
    
    public function useCache() {
        if (empty($this->cacheHelper)) {
            $this->cacheHelper = helper\CacheHelper::getInstance(NET_CACHE_TYPE, CACHE_PX, CACHE_PCONNECT);
        }
    }

    protected function selectStorage($userId) {
        $poolSize = $this->getPoolSize($userId);
        if (empty($this->storage[$poolSize])) {
            if (\defined('LSTORAGE')) {
                $this->storage[$poolSize] = helper\StorageHelper::getInstance(\LSTORAGE, \STORAGE_MASTER_PREFIX . $poolSize, \CACHE_PCONNECT);
            } else {
                $this->storage[$poolSize] = helper\StorageHelper::getInstance(\STORAGE, \STORAGE_MASTER_PREFIX . $poolSize, \CACHE_PCONNECT);
            }
            $this->storage[$poolSize]->setKeySuffix(\PROJECT_NAME);
        }
        return $this->storage[$poolSize];
    }

    protected function getInfo($userId, $key) {
        if (isset(self::$readCache[$userId]) && isset(self::$readCache[$userId][$key])) {
            return self::$readCache[$userId][$key];
        } else {
            $storage = $this->selectStorage($userId);
            $poolSize = $this->getPoolSize($userId);
            $data = $storage->getMD($userId, $key, STORAGE_SLAVE_PREFIX . $poolSize);
            self::$readCache[$userId][$key] = self::unpack($data);
            return self::$readCache[$userId][$key];
        }
    }

    protected function getAllMulti($cmds) {
        $storages = array();
        $keys = array();
        foreach ($cmds as $userId => $key) {
            $poolSize = $this->getPoolSize($userId);
            if (!isset($storages[$poolSize])) {
                $storages[$poolSize] = $this->selectStorage($userId);
            }
            $keys[$poolSize][$userId] = $key;
        }

        $result = array();
        foreach ($storages as $poolSize => $storage) {
            $info = $storage->getMulti($keys[$poolSize]);
            foreach ($info as $item) {
                $rinfo = self::unpack($item);
                $result[] = $rinfo;
            }
        }

        return $result;
    }

    protected function getMultiInfo($userId, $keyArray) {
        $return = array();
        $multiKey = array();
        foreach ($keyArray as $val) {
            if (isset(self::$readCache[$userId]) && isset(self::$readCache[$userId][$val])) {
                $return[$val] = self::$readCache[$userId][$val];
            } else {
                $multiKey[] = $val;
            }
        }
        if (!empty($multiKey)) {
            $storage = $this->selectStorage($userId);
            $slaveName = \STORAGE_SLAVE_PREFIX . $this->getPoolSize($userId);
            $multiData = $storage->getMutilMD($userId, $multiKey);
            if (!empty($multiData)) {
                foreach ($multiData as $key => $data) {
                    if (false === $data) {
                        $data = $storage->getSD($userId, $key, $slaveName);
                        if (false !== $data) {
                            $data = self::unpack($data);
                            $this->setInfo($userId, $key, $data);
                        }
                    } else {
                        $data = self::unpack($data);
                    }
                    self::$readCache[$userId][$key] = $data;
                    $return[$key] = $data;
                }
            }
        }
        return $return;
    }

    protected function setInfo($userId, $key, $info, $set = true) {
        self::$readCache[$userId][$key] = $info;
        $myId = \common\Utils::$loginUserId;
        if (!empty($myId) && $myId != $userId) {
            //当修改的不是自己的数据，加入cas判断
            self::$casCache[$userId][$key][self::INFO_INDEX] = $info;
            return true;
        }

        if ($set && !isset(self::$addCache[$userId][$key][self::INFO_INDEX])) {
            self::$writeCache[$userId][$key][self::INFO_INDEX] = $info;
        } else {
            self::$addCache[$userId][$key][self::INFO_INDEX] = $info;
        }
        return true;
    }

    protected function delInfo($userId, $key) {
        $storage = $this->selectStorage($userId);
        unset(self::$readCache[$userId][$key]);
        unset(self::$writeCache[$userId][$key]);
        unset(self::$addCache[$userId][$key]);
        return $storage->del($userId, $key);
    }

    protected function _setExpire($userId, $key, $time) {
        $storage = $this->selectStorage($userId);
        return $storage->setExpire($userId, $key, $time);
    }

    protected function sync($multi = true) {
        if (empty(self::$writeCache) && empty(self::$addCache) && empty(self::$casCache)) {
            return true;
        }
        $flag = true;
        if (!empty(self::$writeCache)) {
            $keyArray = array();
            foreach (self::$writeCache as $userId => $write) {
                $storage = $this->selectStorage($userId);
                foreach ($write as $key => $value) {
                    $info = self::pack($value[self::INFO_INDEX]);
                    if ($multi) {
                        $keyArray[$userId][$key] = $info;
                    } else {
                        $flag = $storage->setMD($userId, $key, $info);
                    }
                }
            }
            if ($multi) {
                foreach ($keyArray as $userId => $keys) {
                    $storage = $this->selectStorage($userId);
                    $flag = $storage->setMultiMD($userId, $keys);
                }
            }
            unset($keyArray);
            self::$writeCache = array();
        }
        if (!empty(self::$addCache)) {
            foreach (self::$addCache as $userId => $write) {
                $storage = $this->selectStorage($userId);
                foreach ($write as $key => $value) {
                    $info = self::pack($value[self::INFO_INDEX]);
                    $storage->addMD($userId, $key, $info);
                }
            }
            self::$addCache = array();
        }

        if (!empty(self::$casCache)) {
            foreach (self::$casCache as $userId => $write) {
                $storage = $this->selectStorage($userId);
                foreach ($write as $key => $value) {
                    $info = self::pack($value[self::INFO_INDEX]);
                    $storage->setMDCAS($userId, $key, $info);
                }
            }
            self::$casCache = array();
        }
        self::$readCache = array();
        return $flag;
    }

    protected function closeStroge() {
        if (!empty($this->storage)) {
            foreach ($this->storage as $storage) {
                $storage->close();
            }
        }
    }

    public static function pack($data) {
        return util\Serialize::Serialize($data, \SERIALIZE_TYPE);
    }

    public static function unpack($data) {
        if (empty($data)) {
            return array();
        }
        return util\Serialize::Unserialize($data, \SERIALIZE_TYPE);
    }

    private function getPoolSize($userId) {
        if (empty($this->poolSize)) {
            $this->poolSize = $userId % \STORAGE_POOL_SIZE;
        }
        return $this->poolSize;
    }

    protected function getAutoId($userId, $key = self::PUBLIC_AUTOID_KEY) {
        $ids = $this->getInfo($userId, self::AUTO_ID_KEY);
        if (empty($ids) || !isset($ids[$key])) {
            $ids[$key] = 1;
        } else {
            $ids[$key]++;
        }
        $this->setInfo($userId, self::AUTO_ID_KEY, $ids);
        return $ids[$key];
    }

}
