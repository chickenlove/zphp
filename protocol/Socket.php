<?php

namespace framework\protocol;
use framework\util\MessagePacker;
/**
 * 协议转换接口，把不同的传输协议统一转换成框架所需的相关参数
 */
class Socket implements IProtocol {

    private $params;
    public function parse($data){
        $packData = new MessagePacker($data);
        $size = $packData->readInt();
        if(strlen($packData) < $size) {
            return false;
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