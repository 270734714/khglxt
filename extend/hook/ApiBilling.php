<?php
/**
 * User: Jasmine2
 * Date: 2017-7-18 15:22
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace hook;

use think\Db;
use think\Log;
use think\Request;
use think\Cache;
class ApiBilling
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
        $app = $this->request->app;
        $app_prices = Cache::get(config('app_price_prefix') . $app['id']);
        $path = $this->request->pathinfo();
        if (isset($app_prices[$path]) && isset($params->getData()['err_code']) && $params->getData()['err_code'] == 0) {
            $va = $app_prices[$path];
            $data = [
                'app_id'        => $app['id'],
                'resource_id'   => $va['id'],
                'money'        => $va['type'] == '0' ? $va['amount'] : 0,
                'ip'            => $this->request->ip(),
                'create_at'     => time()
            ];
            Db::name('api_spend')->insert($data);
        }
    }
}
