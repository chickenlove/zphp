<?php

namespace ctrl;

use framework\dispatcher\SocketRequestDispatcher;
use framework\config;
use framework\core\IController;
use framework\core\IRequestDispatcher;
use framework\dispatcher\HTTPRequestDispatcher;
use framework\dispatcher\ShellRequestDispatcher;
use framework\view\SmartyView;
use common;

class CtrlBase implements IController {

    protected $dispatcher;

    protected $params = array();

    /**
     * 登陆信息
     *
     * @var array
     */
    protected $loginInfo;
    private $useSmarty = false;

    public function setDispatcher(IRequestDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;

        if ($this->dispatcher instanceof HTTPRequestDispatcher) {
            $this->params = empty($_REQUEST) ? array() : $_REQUEST;
            if (isset($_REQUEST['data'])) {
                unset($this->params['data']);
                if (common\Utils::isAMFRequest()) {
                    $_REQUEST['data'] = amf3_decode($_REQUEST['data']);
                }
                $this->params = $this->params + $this->getJson($_REQUEST, 'data');
            }
        } elseif ($this->dispatcher instanceof ShellRequestDispatcher) {
            $this->params = empty($_SERVER['argv']) ? array() : $_SERVER['argv'];
        } elseif ($this->dispatcher instanceof SocketRequestDispatcher) {
            $this->params = empty($_SERVER['SOCKET_PARAMS']) ? array() : $_SERVER['SOCKET_PARAMS'];
        }
    }

    /**
     * 前置过滤器
     *
     * @return bool
     */
    public function beforeFilter() {
        return true;
    }

    /**
     * 结束过滤器
     *
     */
    public function afterFilter() {
        
    }

    /**
     * 获取用户信息并尝试自动登陆
     *
     */
    protected function setLoginInfo() {
        $this->loginInfo = common\Utils::getLoginInfo();

        if (empty($this->loginInfo)) {
            $apiService = common\ClassLocator::getService('API');
            $userId = $apiService->getLoginUserId();

            if (empty($userId)) {
                $this->loginInfo = null;
            } else {
                $this->loginInfo = common\Utils::setLoginInfo($userId);
            }
        }
    }

    /**
     * 获取当前用户
     *
     * @return entity\User
     */
    protected function getLoginUser() {
        if (empty($this->loginInfo)) {
            throw new common\GameException('0_needLogin');
        }

        $userService = common\ClassLocator::getService('User');
        return $userService->fetchById($this->loginInfo['userId']);
    }

    /**
     * 获取整数参数
     *
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return int
     */
    protected function getInteger(array $params, $key, $default = null, $abs = false, $notEmpty = false) {

        if (!isset($params[$key])) {
            if ($default !== null) {
                return $default;
            }
            throw new common\GameException("no params {$key}", common\ERROR::PARAM_ERROR);
        }

        $integer = isset($params[$key]) ? \intval($params[$key]) : 0;

        if ($abs) {
            $integer = \abs($integer);
        }

        if ($notEmpty && empty($integer)) {
            throw new common\GameException('params no empty', common\ERROR::PARAM_ERROR);
        }

        return $integer;
    }

    /**
     * 获取整数数组参数
     *
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return array
     */
    protected function getIntegers($params, $key, $abs = false, $notEmpty = false) {
        $params = (array) $params;
        $integers = (\array_key_exists($key, $params) && !empty($params[$key])) ? \array_map('intval', (array) $params[$key]) : array();

        if ($abs) {
            $integers = \array_map('abs', $integers);
        }

        if (!empty($notEmpty) && empty($integers)) {
            throw new common\GameException('params no empty', common\ERROR::PARAM_ERROR);
        }

        return $integers;
    }

    /**
     * 获取浮点数参数
     *
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return float
     */
    protected function getFloat($params, $key, $abs = false, $notEmpty = false) {
        $params = (array) $params;

        if (!isset($params[$key])) {
            throw new common\GameException("no params {$key}", common\ERROR::PARAM_ERROR);
        }

        $float = \array_key_exists($key, $params) ? \floatval($params[$key]) : 0;

        if ($abs) {
            $float = \abs($float);
        }

        if (!empty($notEmpty) && empty($float)) {
            throw new common\GameException('params no empty', common\ERROR::PARAM_ERROR);
        }

        return $float;
    }

    /**
     * 获取字符串参数
     *
     * @param array $params
     * @param string $key
     * @param bool $notEmpty
     * @return string
     */
    protected function getString($params, $key, $default = null, $notEmpty = false) {
        $params = (array) $params;

        if (!isset($params[$key])) {
            if (null !== $default) {
                return $default;
            }
            throw new common\GameException("no params {$key}", common\ERROR::PARAM_ERROR);
        }

        $string = \trim($params[$key]);

        if (!empty($notEmpty) && empty($string)) {
            throw new common\GameException('params no empty', common\ERROR::PARAM_ERROR);
        }

        return \addslashes($string);
    }

    /**
     * 获取字符串数组参数
     *
     * @param array $params
     * @param string $key
     * @param bool $notEmpty
     * @return array
     */
    protected function getStrings($params, $key, $notEmpty = false) {
        $params = (array) $params;
        $strings = (\array_key_exists($key, $params) && !empty($params[$key])) ? \array_map('trim', (array) $params[$key]) : array();

        if (!empty($notEmpty) && empty($strings)) {
            throw new common\GameException('params no empty', common\ERROR::PARAM_ERROR);
        }

        return \array_map("addslashes", $strings);
    }

    protected function getJson(array $params, $key, $default = null, $array = true) {

        if (!isset($params[$key])) {
            if (null !== $default) {
                return $default;
            }
            throw new common\GameException("no params {$key}", common\ERROR::PARAM_ERROR);
        }

        if (\is_array($params[$key]) || \is_object($params[$key])) {
            return $params[$key];
        }

        return \json_decode($params[$key], $array);
    }

    protected function useSmarty() {
        if (!$this->useSmarty) {
            // 初始化Smarty配置
            $smartyConfig = new config\SmartyConfiguration(
                            common\Utils::mergePath(\ROOT_PATH, \SMARTY_LIB_PATH) . \DIRECTORY_SEPARATOR,
                            common\Utils::mergePath(\ROOT_PATH, \SMARTY_CACHE_PATH) . \DIRECTORY_SEPARATOR,
                            common\Utils::mergePath(\ROOT_PATH, \SMARTY_COMPILE_PATH) . \DIRECTORY_SEPARATOR,
                            common\Utils::mergePath(\ROOT_PATH, \SMARTY_TEMPLATE_PATH) . \DIRECTORY_SEPARATOR,
                            common\Utils::mergePath(\ROOT_PATH, \SMARTY_CONFIG_PATH) . \DIRECTORY_SEPARATOR
            );
            SmartyView::setConfiguration($smartyConfig);
        }
    }

}
