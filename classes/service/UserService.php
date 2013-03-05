<?php

namespace service;

use entity,
    common;


class UserService {


    private $dao;

    public function __construct() {
        $this->dao = common\ClassLocator::getDao('User');
    }

    public function add(entity\User $user) {
        $this->dao->add($user);
        return $user;
    }


    public function fetchById($id) {
        return $this->dao->fetchById($id);
    }

    public function remove($userId) {
        return $this->dao->remove($userId);
    }

    public function sync() {
        return $this->dao->async();
    }

    public function close() {
        return $this->dao->close();
    }

}
