<?php

namespace framework\socket\adapter;

use framework\socket\Connection;
use \React\EventLoop\Factory as eventLoop,
    \React\Socket\Server as server;

class React implements  ISocket {
    private $protocol;
    private $config;
    private $serv;
    private $loop;

    public function __construct($config) {
        $loop = eventLoop::create();
        $this->loop = $loop;
        $this->serv = new server($loop);
        $this->config = $config;
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function run() {
        $protocol = $this->protocol;
        $protocol->onStart($this->serv);
        $this->serv->on('connection', function ($conn) use ($protocol) {
            $protocol->onConnect($conn);
            $conn->on('data', function ($datas) use ($conn, $protocol) {
                $protocol->onReceive($conn, $datas);
            });

            $conn->on('end', function () use ($conn, $protocol) {
                $protocol->onClose($conn);
                $conn->end();
            });

            $conn->on('close', function() use ($protocol){
                $protocol->onShutdown();
            });
        });
        $this->serv->listen($this->config['host'], $this->config['port']);
        $this->loop->run();
    }

}