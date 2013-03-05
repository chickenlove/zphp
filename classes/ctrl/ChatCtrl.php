<?php

namespace ctrl;

use framework\socket\Connection,
    common;

class ChatCtrl extends CtrlBase
{

    private $userInfo;
    private $userId;

    public function beforeFilter()
    {
        if($this->dispatcher->ctrlMethodName !== 'check') {
            if(empty($this->userInfo)) {
                $this->check();
            }
        }
    }

    public function check() {
        $userId = $this->getInteger($this->params, 'uid');
        $token = $this->getString($this->params, 'token');
        if($token === ADMIN_TOKEN) {
            return;
        }
        $conn = $this->params['conn'];
        $userService = common\ClassLocator::getService('User');
        $userInfo = $userService->fetchById($userId);
        if($userInfo->token != $token) {
            $data = \json_encode([
                'type'=>'loginfaild',
                'from'=>$userId
            ]);
            $conn->write("{$data}\0");
            $conn->end();
            return false;
        }
        if(Connection::check($userId)) {
            $data = \json_encode([
                'type'=>'userexists',
                'from'=>$userId
            ]);
            $conn->write("{$data}\0");
            $conn->end();
            return false;
        }
        Connection::add($userId, $conn);
        $this->userInfo = $userInfo;
        $this->userId = $userId;
    }

    public function init() {
        $data = \json_encode([
            'type'=>'loginsuccess',
            'from'=>$this->userId,
            'name'=>$this->userInfo->name,
        ]);
        Connection::boardcast($data);
    }

    public function sendAll() {
        $message = $this->getString($this->params, 'message');
        $data = \json_encode([
            'type'=>'sendAll',
            'from'=>$this->userId,
            'message'=>$message,
        ]);
        Connection::boardcast($data."\0", $this->userId);
    }

    public function getList() {
        $olList = Connection::getAll();
        $userList = [];
        $userService = common\ClassLocator::getService('User');
        foreach($olList as $ol) {
            if($ol['uid'] == $this->userId) {
                $userList[$ol['uid']] = $this->userInfo->getHash();
                continue;
            }
            $userInfo = $userService->fetchById($ol['uid']);
            if(!empty($userInfo)) {
                $userList[$ol['uid']] = $userInfo->getHash();
            }
        }
        $names = [
            'type'=>'userlist',
            'userlist' =>$userList,
        ];
        $names = \json_encode($names);
        Connection::boardcast($names."\0", $this->userId);
    }

    public function sendTo() {
        $toId = $this->getInteger($this->params, 'toid');
        if(Connection::check($toId)) {
            $message = $this->getString($this->params, 'message');
            $data = [
                'type'=>'sendTo',
                'from'=>$this->userId,
                'message'=>$message,
            ];
            Connection::send($toId, $data."\0");
        }
    }


    public function heartBeat() {
        Connection::heartBeat($this->userId);
    }

    public function activeCheck() {
        Connection::activeCheck();
    }

    public function loginOut() {
        Connection::close($this->userId);
    }



}
