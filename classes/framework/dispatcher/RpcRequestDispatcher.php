<?php


namespace framework\dispatcher;
use framework\core\Context;

class RpcRequestDispatcher extends RequestDispatcherBase {

    private $ctrlClassName;
    private $ctrlMethodName;
    public function __construct() {
        $this->display = false;
    }

    public function run() {
        $service = new \Yar_Server(new RpcRequestDispatcher());
        $service->handle();
    }

    public function api($params) {
        $this->ctrlClassName = $params['a'];
        $this->ctrlMethodName = $params['m'];
        $_REQUEST = $params['params'];
        return $this->dispatch();
    }

    public function getCtrlClassName() {
        return $this->ctrlClassName;
    }

    public function getCtrlMethodName() {
        return $this->ctrlMethodName;
    }

}