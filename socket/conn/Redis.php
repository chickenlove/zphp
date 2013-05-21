<?php

namespace framework\socket\conn;

/**
 *  redis 容器
 */
class Redis implements IConn{

    private $redis;

    public function __construct($redis) {
        $this->redis = $redis;
    }


    public function addFd($fd, $uid=0) {
        return $this->redis->set($this->getKey($fd, 'fu'), $uid);
    }


    public function getUid($fd) {
        return $this->redis->get($this->getKey($fd, 'fu'));
    }

    public function add($uid, $fd) {
        $uinfo = $this->get($uid);
        if(!empty($uinfo)) {
            $this->delete($uid);
        }
        $data = [
            'fd'=> $fd,
            'time'=> time(),
            'types'=> ['ALL'=>1]
        ];

        $this->redis->set($this->getKey($uid), \json_encode($data));
        $this->redis->hSet($this->getKey('ALL'), $uid, $fd);
    }

    public function addChannel($uid, $channel) {
        $uinfo = $this->get($uid);
        $uinfo['types'][$channel] = 1;
        if($this->redis->hSet($this->getKey($channel), $uid, $uinfo['fd'])) {
            $this->redis->set($this->getKey($uid), json_encode($uinfo));
        }
    }

    public function getChannel($channel='ALL') {
        return $this->redis->hGetAll($this->getKey($channel));
    }

    public function get($uid) {
        $data = $this->redis->get($this->getKey($uid));
        if(empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function uphb($uid) {
        $uinfo = $this->get($uid);
        if(empty($uinfo)) {
            return false;
        }
        $uinfo['time'] = time();
        return $this->redis->set($this->getKey($uid), json_encode($uinfo));
    }

    public function heartbeat($uid, $ntime = 60) {
        $uinfo = $this->get($uid);
        if(empty($uinfo)) {
            return false;
        }
        $time = time();
        if($time - $uinfo['time'] > $ntime) {
            $this->delete($uinfo['fd'], $uid);
            return false;
        }
        return true;
    }

    public function delete($fd, $uid=null, $old=true) {
        if(null === $uid) {
            $uid = $this->getUid($fd);
        }
        if($old) {
            $this->redis->delete($this->getKey($fd, 'fu'));
        }
        if(empty($uid)) {
            return ;
        }
        $uinfo = $this->get($uid);
        if(!empty($uinfo)) {
            $this->redis->delete($this->getKey($uid));
            foreach($uinfo['types'] as $type=>$val) {
                $this->redis->hDel($this->getKey($type), $uid);
            }
        }
    }

    private function getKey($uid, $prefix='uf') {
        return "{$prefix}_{$uid}_".PROJECT_NAME;
    }

    public function clear() {
        $this->redis->flushAll();
    }
}