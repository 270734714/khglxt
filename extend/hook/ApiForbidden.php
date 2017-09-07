<?php
/**
 * User: Jasmine2
 * Date: 2017-7-18 16:32
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace hook;

use think\Cache;
use think\Log;
use think\Request;
use think\exception\HttpResponseException;
/**
 * Class ApiForbidden
 *
 * @package hook
 * 检测接口禁用
 */
class ApiForbidden
{
    /**
     * 当前请求对象
     *
     * @var Request
     */
    protected $request;

    protected $except = [
        'api/access_token'
    ];
    /**
     * 行为入口
     *
     * @param $params
     */
    public function run(&$params)
    {
        $this->request = Request::instance();
        if(!in_array($this->request->pathinfo(), $this->except)) {
            $data = Cache::get('resources');
            if(!in_array($this->request->pathinfo(), $data) ) {
                $result = [
                    'err_code'  => '-1',
                    'err_msg'   => 'The interface you requested is currently unavailable[1].',
                ];
                throw new HttpResponseException(json($result));
            }
            // 检测应用是否可访问接口
            $app = $this->request->app;
            $acl = Cache::get(config('app_acl_prefix') . $app['id']);
            $acl = array_column($acl, 'path');
            if(!in_array($this->request->pathinfo(), $acl)) {
                $result = [
                    'err_code'  => '-1',
                    'err_msg'   => 'The interface you requested is currently unavailable[2].',
                ];
                throw new HttpResponseException(json($result));
            }
            $app_prices = Cache::get(config('app_price_prefix') . $app['id']);
            $path = $this->request->pathinfo();
            if (isset($app_prices[$path])) {
                $va = $app_prices[$path];
                if($va['type'] == '1' && time() > $va['expire_at']) {
                    $result = [
                        'err_code'  => '9999',
                        'err_msg'   => 'The Service you requested has expired.',
                    ];
                    throw new HttpResponseException(json($result));
                }
            }
        }
    }
}
