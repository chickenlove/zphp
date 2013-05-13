<?php


namespace framework\dispatcher;
use framework\util\Daemon;
use framework\protocol;

/**
 * 用于执行控制台脚本的请求转发器。
 */
class ShellRequestDispatcher extends RequestDispatcherBase {

    private $daemon = false;

    public function __construct() {
        if(empty($this->protocol)) {
            $this->protocol = new protocol\Cli();
            $this->protocol->parse($_SERVER['argv']);
            if("-d" == end($_SERVER['argv'])) {
                $this->daemon = true;
            }
        }
    }

    public function dispatch() {
        if($this->daemon) {
            $deamon = new Daemon($GLOBALS['DAEMON_CONFIG']);
            $deamon->start();
        }
        parent::dispatch();
    }

}
