<?php


namespace framework\dispatcher;
use framework\util\Daemon;
use framework\core\Context;

/**
 * socket控制器
 */
class SocketRequestDispatcher extends RequestDispatcherBase {

    private $ctrlClassName;
    private $ctrlMethodName;
    public function __construct() {
        if("-d" == end($_SERVER['argv'])) {
            $deamon = new Daemon($GLOBALS['DAEMON_CONFIG']);
            $deamon->start();
        }
        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($loop);
        $dispatcher = $this;
        $socket->on('connection', function ($conn) use ( $dispatcher) {
            $conn->on('data', function ($datas) use ($conn, $dispatcher) {
                $datas = \trim($datas);
                if(empty($datas)) {
                    return ;
                }
                if('<policy-file-request/>' == $datas) {
                    $policyData = file_get_contents(Context::getRootPath().DIRECTORY_SEPARATOR.'webroot'.DIRECTORY_SEPARATOR.'crossdomain.xml');
                    $conn->write("$policyData\0");
                    $conn->end();
                    return;
                }
                $messagePack = \defined('MESSAGE_PACK') ? MESSAGE_PACK : 'json';
                $cmds = \framework\Util\Serialize::Unserialize($datas, $messagePack);
                $_SERVER['SOCKET_PARAMS'] = $cmds['params'];
                $dispatcher->ctrlClassName = $cmds['a'];
                $dispatcher->ctrlMethodName = $cmds['m'];
                $dispatcher->dispatch();
            });

            $conn->on('end', function () use ($conn) {
                $conn->end();
            });
        });
        $socket->listen(HOST, PORT);
        $loop->run();
    }

    public function getCtrlClassName() {
        return $this->ctrlClassName;
    }

    public function getCtrlMethodName() {
        return $this->ctrlMethodName;
    }

}