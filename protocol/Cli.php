<?php

namespace framework\protocol;

class Cli implements IProtocol {
    private $ctrlClassName = 'ShellCtrl';
    private $ctrlMethodName = 'main';
    private $params;

    public function parse($data){
        if(!empty($data[1])) {
            if (\preg_match('/^([a-z_]+)\.([a-z_]+)$/i', $data[1], $arr)) {
                $this->ctrlClassName = $arr[1] . 'Ctrl';
                $this->ctrlMethodName = $arr[2];
            }
        }

        unset($data[0]);
        unset($data[1]);
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