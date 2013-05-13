<?php

namespace framework\view;

use framework\core\IView;

/**
 * amf视图，向用户输出amf
 */
class AMFView implements IView {

    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    /**
     * 获取数据
     *
     * @return mixed
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * 设置数据
     *
     * @param mixed $model
     */
    public function setModel($model) {
        return $this->model = $model;
    }

    public function getXml() {
        
    }

    /**
     * 展示视图
     *
     */
    public function display() {
        \header("Content-Type: application/amf; charset=utf-8");
        echo \amf3_encode($this->model);
    }

}