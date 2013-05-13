<?php

namespace framework\protocol;

class Rpc implements IProtocol {
    private $ctrlClassName = 'IndexCtrl';
    private $ctrlMethodName = 'main';
    private $params;

    public function parse($data){
        $this->ctrlClassName = $data['a'];
        $this->ctrlMethodName = $data['m'];
        $this->params = $data['params'];
        $_REQUEST = $data['params'];
    }
    public function getCtrlName(){
        return $this->ctrlClassName;
    }
    public function getMethodName(){
        return $this->ctrlMethodName;
    }
    public function getParams(){
        return $this->params;
    }
}