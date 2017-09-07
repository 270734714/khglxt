<?php

/**
 * User: Jasmine2
 * Date: 2017-7-17 16:33
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
namespace app\api\controller;
use controller\BaseApi;
use think\Cache;
use think\Db;
use think\Log;

class Auth extends BaseApi
{
    /**
     * @return \controller\Redirect|\think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function accessToken()
    {
        $app_id = $this->request->request('app_id', false, 'trim');
        $app_secret = $this->request->request('app_secret', false, 'trim');
        if($app_id && $app_secret){
            $app = Db::name('auth_client')->where(['client_id' => $app_id, 'client_secret' => $app_secret, 'status' => 99])->find();
            if(!empty($app)) {
                $access_token = get_random_string('',128);
                Cache::set($access_token, $app, 7200);
                return $this->sendSuccess([
                    'access_token'  => $access_token,
                    'expires_in'    => 7200
                ]);
            } else {
                return $this->sendError('402','invalid app_id');
            }
        } else {
            return $this->sendError('401','app_id and app_secret can\'t be null');
        }
    }
}