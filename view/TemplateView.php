<?php

namespace framework\view;

use framework\core\Context;
use framework\core\IView;

/**
 * 用原生的php展示数据
 */
class TemplateView implements IView {


    /**
     * 模板文件
     *
     * @var string
     */
    private $fileName;

    /**
     * 模板数据
     *
     * @var mixed
     */
    private $model;

    /**
     * 构造函数
     *
     * @param String $fileName Smarty模版文件名
     * @param mixed $model 用于展示的数据
     */
    public function __construct($model = null, $fileName=null) {
        $this->fileName = $fileName;
        $this->model = $model;
    }

    /**
     * @return mixed|null
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return mixed
     */
    public function setModel($model) {
        return $this->model = $model;
    }

    /**
     * 展示视图
     *
     */
    public function display() {
        header("Content-Type: text/html; charset=utf-8");
        $this->output();
    }

    /**
     * 输出
     *
     */
    public function output() {
        $fileName = Context::getRootPath(). TEMPLATE_PATH . $this->fileName;
        if(!is_file($fileName)) {
            throw new \Exception("no file {$fileName}");
        }
        if(!empty($this->model)) {
            extract($this->model);
        }
        include "{$fileName}";
    }
}