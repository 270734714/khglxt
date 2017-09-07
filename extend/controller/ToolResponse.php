<?php
/**
 * User: Jasmine2
 * Date: 2017-6-20 11:17
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace controller;

use think\Log;
use think\Response;
trait ToolResponse
{
    /**
     * 默认返回资源类型
     *
     * @var string
     */
    protected $restDefaultType = 'json';
    /**
     * 设置响应类型
     *
     * @param  null $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }
    /**
     * 失败响应
     *
     * @param  int    $error
     * @param  string $message
     * @param  int    $code
     * @param  array  $data
     * @param  array  $headers
     * @param  array  $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendError($error = 400, $message = 'error', $code = 400, $data = [], $headers = [], $options = [])
    {
        $responseData['err_code'] = (int)$error;
        $responseData['err_msg'] = (string)$message;
        if (!empty($data)) { $responseData['data'] = $data;
        }
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }
    /**
     * 成功响应加密
     *
     * @param  array  $data
     * @param  string $message
     * @param  int    $code
     * @param  array  $headers
     * @param  array  $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\Xml
     */
    public function sendSuccessEncrypt($data = [], $message = 'success', $code = 200, $headers = [], $options = [], $other = [])
    {
        $responseData['err_code'] = 0;
        $responseData['err_msg'] = (string)$message;
        if (!empty($data)) { $responseData['data'] = api_encrypt($data);
        }
        if (!empty($other)) { $responseData['other'] = $other;
        }
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }
    /**
     * 成功响应,不加密
     *
     * @param  array  $data
     * @param  string $message
     * @param  int    $code
     * @param  array  $headers
     * @param  array  $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\Xml
     */
    public function sendSuccess($data = [], $message = 'success', $code = 200, $headers = [], $options = [])
    {
        $responseData['err_code'] = 0;
        $responseData['err_msg'] = (string)$message;
        if (!empty($data)) {
            $responseData['data'] = $data;
        } else {
            $responseData['data'] = [];
        }
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }
    /**
     * 重定向
     *
     * @param  $url
     * @param  array $params
     * @param  int   $code
     * @param  array $with
     * @return Redirect
     */
    public function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        return $response;
    }
    /**
     * 响应
     *
     * @param  $responseData
     * @param  $code
     * @param  $headers
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    public function response($responseData, $code, $headers)
    {
        if (!isset($this->type) || empty($this->type)) { $this->setType();
        }
        return Response::create($responseData, $this->type, $code, $headers);
    }
}
