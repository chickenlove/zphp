<?php

namespace framework\socket\conn;


interface IConn {

    public function addFd($fd, $uid);
    public function getUid($fd);
    public function add($uid, $fd);
    public function addChannel($uid, $channel);
    public function getChannel($channel);
    public function get($uid);
    public function uphb($uid);
    public function heartbeat($uid, $ntime);
    public function delete($fd, $uid, $old);
    public function clear($fd, $uid);


}