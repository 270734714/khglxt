<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

return [
    // 应用初始化
    'app_init'     => [],
    // 应用开始
    'app_begin'    => [],
    // 模块初始化
    'module_init'  => [],
    // 操作开始执行
    'action_begin' => [
        'hook\\AccessAuth',
    ],
    // 视图内容过滤
    'view_filter'  => ['hook\\FilterView'],
    // 日志写入
    'log_write'    => [],
    // 应用结束
    'app_end'      => [],
    'response_end' => [
    ],
];
