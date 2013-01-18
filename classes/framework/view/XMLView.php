<?php

namespace framework\view;

use framework\core\IView;

/**
 * xml视图，向用户输出xml
 */
class XMLView implements IView {

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

    public function xml_encode() {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml.= "\n<root>\n";
        $xml.= $this->data_to_xml($this->model);
        $xml.= "<root>\n";
        return $xml;
    }

    private  function data_to_xml($data) {
        $xml = "";
        foreach ($data as $key => $val) {
            \is_numeric($key) && $key = "item id=\"$key\"";
            $xml.="<{$key}>";
            $xml.= ( \is_array($val) || \is_object($val)) ? $this->data_to_xml($val) : $val;
            list($key, ) = \explode(' ', $key);
            $xml.="</{$key}>\n";
        }

        return $xml;
    }

    /**
     * 展示视图
     *
     */
    public function display() {
        header("Content-Type:text/xml; charset=utf-8");
        echo $this->xml_encode();
    }

}