<?php


namespace framework\dispatcher;

use framework\core\IView;
use framework\core\IRequestDispatcher;
use framework\core\IController;
use framework\core\Context;

/**
 * IRequestDispatcher的抽象实现，它实现了dispatch方法，
 * 并且定义了getCtrlClassName和getCtrlMethodName两个抽象方法，其子类只需实现这两个方法即可。
 */
abstract class RequestDispatcherBase implements IRequestDispatcher {

    /**
     * 默认动作
     *
     * @var string
     */
    protected $protocol;
    protected $display = true;

    public function dispatch() {
        $ctrlClass = Context::getCtrlNamespace() . "\\" . $this->getCtrlClassName();
        $ctrlMethod = $this->getCtrlMethodName();

        $ctrl = new $ctrlClass();
        $filtered = false;

        if ($ctrl instanceof IController) {
            $ctrl->setDispatcher($this);
            $filtered = !$ctrl->beforeFilter();
        }

        $exception = null;

        if (!$filtered) {
            try {
                $view = $ctrl->$ctrlMethod();
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        if ($ctrl instanceof IController) {
            $ctrl->afterFilter();
        }

        if ($exception != null) {
            throw $exception;
        }

        if ($view instanceof IView) {
            if($this->display) {
                $view->display();
            } else {
                return $view->getModel();
            }
        }

    }

    public function getProtocol() {
        return $this->protocol;
    }

    /**
     * 获取控制器类名
     *
     * @return String
     */
    public function getCtrlClassName(){
        return $this->protocol->getCtrlName();
    }

    /**
     * 获取控制器方法名
     *
     * @return String
     */
    public function getCtrlMethodName(){
        return $this->protocol->getMethodName();
    }

    public function getParams() {
        return $this->protocol->getParams();
    }
}
