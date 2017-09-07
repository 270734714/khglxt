<?php
/**
 * User: Jasmine2
 * Date: 2017-7-18 11:05
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller;


use controller\BaseApi;

class Index extends BaseApi
{
    public function index(){
        return $this->sendSuccess('welcome');
    }
}