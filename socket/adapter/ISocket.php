<?php

namespace framework\socket\adapter;


interface ISocket {

    public function setProtocol($protocol);
    public function run();
}