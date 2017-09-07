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
class ApiIpBan
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
        $list = $app['ip_white_list'];
        $list = explode(',', $list);
        $checked = false;
        foreach ($list as $item) {
            if(checkIp($this->request->ip, $item)) {
                $checked = true;
                break;
            }
        }
        if(!$checked) {
            $result = [
                'err_code'  => '9998',
                'err_msg'   => 'The ip not in the white list.',
            ];
            throw new HttpResponseException(json($result));
        }
    }
}
