<?php
namespace sms;
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
interface Sms
{
    public function send($mobiles, $content);
}
