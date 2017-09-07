<?php

/**
 * User: Jasmine2
 * Date: 2017-7-17 17:17
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
namespace app\api_v1\controller;
use controller\BaseApi;

class Test extends BaseApi
{
    public function i(){
        $ip = '117.36.75.174';
        $range = '0.0.0.0/8';
        $data = checkIp($ip, $range);
//        throw new \Exception('test');
        return $this->sendSuccess($this->request->ip());
    }
}