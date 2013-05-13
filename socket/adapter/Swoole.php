<?php

namespace framework\socket\adapter;


class Swoole implements  ISocket {
    private $protocol;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->serv = swoole_server_create($config['host'], $config['port'], SWOOLE_PROCESS);
        swoole_server_set($this->serv, $config['params']);
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function run() {
        swoole_server_handler($this->serv, 'onStart', array($this->protocol, 'onStart'));
        swoole_server_handler($this->serv, 'onConnect', array($this->protocol, 'onConnect'));
        swoole_server_handler($this->serv, 'onReceive', array($this->protocol, 'onReceive'));
        swoole_server_handler($this->serv, 'onClose', array($this->protocol, 'onClose'));
        swoole_server_handler($this->serv, 'onShutdown', array($this->protocol, 'onShutdown'));
        swoole_server_handler($this->serv, 'onTimer', array($this->protocol, 'onTimer'));
        if(!empty($this->config['times'])) {
            foreach($this->config['times'] as $time) {
                swoole_server_addtimer($this->serv, $time);
            }
        }
        swoole_server_start($this->serv);

    }

}