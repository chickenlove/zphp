<?php

namespace framework\protocol;

/**
 * 协议转换接口，把不同的传输协议统一转换成框架所需的相关参数
 */
interface IProtocol {

    function parse($data);
    function getCtrlName();
    function getMethodName();
    function getParams();
}