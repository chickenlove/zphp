<?php

// -*-coding:utf-8; mode:php-mode;-*-

namespace framework\dispatcher;
use framework\protocol;

/**
 * HTTP请求转发器，IRequestDispacher的一个实现，用于分发HTTP的请求。
 * 当GET或者POST信息包含类似于act=CtrlName.methodName时，将执行CtrlName类的methodName方法。
 * 也可以指定  a=CtrlName&m=methodName ,如果没有指定m参数，将默认为 main
 */
class HTTPRequestDispatcher extends RequestDispatcherBase {

    public function __construct() {
        if(empty($this->protocol)) {
            $this->protocol = new protocol\Http();
            $this->protocol->parse($_REQUEST);
        }
    }

}
