<?php
/**
 * User: Jasmine2
 * Date: 2017-7-27 20:14
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace exception\handle;


use think\exception\Handle;
use Exception;
use think\Response;

class ApiException extends Handle
{
    public function render(Exception $e)
    {
        $responseData = [
            'err_code'      => '-1',
            'err_msg'       => config('error_message')
        ];
        return Response::create($responseData, 'json', 200, []);
    }
}