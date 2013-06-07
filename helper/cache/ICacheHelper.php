<?php

namespace framework\helper\cache;

/**
 *  cache 接口，申明了必需的一些方法
 */
interface ICacheHelper {

    public function enable();

    public function selectDb($db);

    public function add($key, $value);

    public function set($key, $value);

    public function get($key);

    public function delete($key);

    public function increment($key, $step = 1);

    public function decrement($key, $step = 1);

    public function clear();
}
