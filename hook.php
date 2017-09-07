<?php
/**
 * User: Jasmine2
 * Date: 2017-7-7 11:27
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
$target = '/alidata/www/dev_bpm'; // 生产环境web目录
$token = 'dev_bpm';
$json = json_decode(file_get_contents('php://input'), true);
if (empty($_SERVER['HTTP_X_GITLAB_TOKEN']) || $_SERVER['HTTP_X_GITLAB_TOKEN'] !== $token) {
    exit('error request');
}
$repo = $json['repository']['name'];
$cmd = "cd $target && git pull origin dev";
$out = system($cmd);