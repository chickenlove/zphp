<?php
use framework\core\Context;
$rootPath = realpath('..');
require ($rootPath . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "setup.php");
Context::setRootPath($rootPath);
$infPath = Context::getRootPath() . DIRECTORY_SEPARATOR . 'inf' . DIRECTORY_SEPARATOR . 'default';
Context::setInfoPath($infPath);
Context::initialize(); 
(new \framework\dispatcher\RpcRequestDispatcher())->run();

/**
	rpc_client demo

	$params = [
        'a'=>'you ctrl',
        'm'=>'your ctrl method',
        'params'=>[
            your params
        ]
    ];
    $client = new Yar_Client("http://host/rpc.php");
    $result = $client->api($params);
    var_dump($result);
*/