<?php

namespace dao;

use entity;

class UserDao extends UserDaoBase {

    public function __construct() {
        parent::__construct('entity\\User', true);
    }

    public function add(entity\User $user, $set = true) {
        $infoKey = $this->getInfoKey();
        $this->setInfo($user->uid, $infoKey, $user, $set);
        return $user;
    }

    public function fetchById($userId) {
        $infoKey = $this->getInfoKey();
        return $this->getInfo($userId, $infoKey);
    }

    public function remove($userId) {
        $infoKey = $this->getInfoKey();
        $this->delInfo($userId, $infoKey);
    }


    public function setExpire($userId, $key, $time) {
        return $this->_setExpire($userId, $key, $time);
    }

    /**
     * 数据同步
     *
     */
    public function async() {
        return parent::sync();
    }

    public function close() {
        return parent::closeStroge();
    }

}
