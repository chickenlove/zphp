<?php

namespace ctrl;

class IndexCtrl 
{

    public function index() {

        echo 'hello world';
    }

    public function addQueue() {
    	$host = "127.0.0.1";
    	$port = 6379;
    	$name = "redis_queue";
    	$key = "queue_key";
    	$storageSConfig = new \framework\config\RedisConfiguration($host, $port);
        \framework\manager\RedisManager::addConfigration($name, $storageSConfig);
    	$queue = \framework\helper\QueueHelper::getInstance('Redis', $name, true);
    	$data = json_encode($_REQUEST);
    	$queue->addQueue($key, $data);

    }

    public function getQueue() {
    	if('cli' !== PHP_SAPI ) {
    		echo 'forbidden';
    		return;
    	}
    	$host = "127.0.0.1";
    	$port = 6379;
    	$name = "redis_queue";
    	$key = "queue_key";
    	$storageSConfig = new \framework\config\RedisConfiguration($host, $port);
        \framework\manager\RedisManager::addConfigration($name, $storageSConfig);
    	$queue = \framework\helper\QueueHelper::getInstance('Redis', $name, true);

    	while (true) {
    		$data =  $queue->getQueue($key);
    		if(empty($data)) {
    			sleep(1);
    		} else {
    			echo $data;
    		}
    	}
    	
    }

}