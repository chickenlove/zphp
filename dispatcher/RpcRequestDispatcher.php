<?php


namespace framework\dispatcher;
use framework\protocol;

class RpcRequestDispatcher extends RequestDispatcherBase {

    public function __construct() {
        if(empty($this->protocol)) {
            $this->protocol = new protocol\Rpc();
            $this->display = false;
        }
    }

    public function api($params) {
        $this->protocol->parse($params);
        return $this->dispatch();
    }



}
