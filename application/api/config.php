<?php
/**
 * User: Jasmine2
 * Date: 2017-8-19 16:09
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
return [
    'app_debug' => false,
    // 应用Trace
    'app_trace' => false,
    'exception_handle' => '\\exception\\handle\\ApiException',
    'error_message'     => '系统异常,请稍候~',
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type' => 'File',
        // 日志保存目录
        'path' => LOG_PATH . '/api/',
        // 日志记录级别 log,error,info,sql,notice,alert,debug
        'level' => ['error', 'log'],
        // 单独记录
        'apart_level' => ['error','log'],
    ],
];