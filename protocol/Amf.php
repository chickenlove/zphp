<?php

namespace framework\protocol;

/**
 * 协议转换接口，把不同的传输协议统一转换成框架所需的相关参数
 */
class Amf implements IProtocol {
    private $ctrlClassName = 'IndexCtrl';
    private $ctrlMethodName = 'main';
    private $params;
    public function parse($data){
        $data = \amf3_decode($data);
        if(isset($data['a'])) {
            $this->ctrlClassName = $data['a'];
            unset($data['a']);
        }
        
        if(isset($data['m'])) {
            $this->ctrlMethodName = $data['m'];
            unset($data['m']);
        }
        $this->params = $data;
    }

    public function getCtrlName(){
        return $this->ctrlClassName;
    }

    public function getMethodName(){
        return $this->getMethodName();
    }

    public function getParams(){
        return $this->params;
    }
}