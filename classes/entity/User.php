<?php
namespace entity;

class User{
    public $uid;
    public $name;
    public $password;

    public function getHash() {
        return [
            'name'=>$this->name
        ];
    }
}