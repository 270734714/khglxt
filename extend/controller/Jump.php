<?php
/**
 * User: Jasmine2
 * Date: 2017-7-4 12:56
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace controller;

use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use think\Url;
use think\View as ViewTemplate;
trait Jump
{
    /**
     * 操作成功跳转的快捷方法
     *
     * @access protected
     * @param  mixed   $msg    提示信息
     * @param  string  $url    跳转的URL地址
     * @param  mixed   $data   返回的数据
     * @param  integer $wait   跳转等待时间
     * @param  array   $header 发送的Header信息
     * @return void
     */
    protected function successRedirect($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $code = 1;
        if (is_numeric($msg)) {
            $code = $msg;
            $msg  = '';
        }
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => "/admin.html#" . $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $result = ViewTemplate::instance(Config::get('template'), Config::get('view_replace_str'))
                ->fetch(Config::get('dispatch_success_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     *
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $isAjax = Request::instance()->isAjax();
        return $isAjax ? Config::get('default_ajax_return') : Config::get('default_return_type');
    }
}
