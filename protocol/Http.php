<?php

namespace framework\protocol;

class Http implements IProtocol {
    private $ctrlClassName = 'IndexCtrl';
    private $ctrlMethodName = 'main';
    private $params;

    public function parse($data){
        if (isset($data['act'])) {
            $act = $data['act'];
            if (\preg_match('/^([a-z_]+)\.([a-z_]+)$/i', $act, $arr)) {
                $this->ctrlClassName = \ucfirst($arr[1]) . 'Ctrl';
                $this->ctrlMethodName = $arr[2];
            }
            unset($data['act']);
        } else {
            if(isset($data['c'])) {
                $this->ctrlClassName = \str_replace('/', '\\', $data['c']);
                unset($data['c']);
            }

            if(isset($data['m'])) {
                $this->ctrlMethodName = $data['m'];
                unset($data['m']);
            }

        }
        $this->params = $data;
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