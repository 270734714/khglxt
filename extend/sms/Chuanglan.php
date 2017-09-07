<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
namespace sms;
/**
 * 创蓝短信渠道接口
 * User: Jasmine
 * Date: 2017-6-2
 * Time: 16:01
 */
use GuzzleHttp\Client;
use app\models\Sms as SmsModel;
use think\Db;
use think\Log;
use think\Request;

class Chuanglan implements Sms
{
    private $gate_way = null;
    private $client = null;
    private $user;
    private $password;
    private $sign;
    private $extno;

    public function __construct($sms_type = "captcha")
    {
        if($this->gate_way === null) {
            if($sms_type == "captcha") {
                $this->gate_way     = sysconf('sms_cl_captcha_gateway');
                $this->user         = sysconf('sms_cl_captcha_user');
                $this->password     = sysconf('sms_cl_captcha_password');
                $this->sign         = sysconf('sms_cl_captcha_sign');
                $this->extno        = sysconf('sms_cl_captcha_extno');
            } else {
                $this->gate_way = sysconf('sms_cl_gateway');
                $this->user = sysconf('sms_cl_user');
                $this->password = sysconf('sms_cl_password');
                $this->sign = sysconf('sms_cl_sign');
                $this->extno = sysconf('sms_cl_extno');
            }
        }
        if($this->client === null) {
            $this->client = new Client(
                [
                'base_uri'  => $this->gate_way,
                'headers'   => [
                    'Content-type'  => 'application/json'
                ]
                ]
            );
        }
    }

    public function send($mobiles, $content)
    {
        if(is_array($mobiles)) {
            $mobile = implode(',', $mobiles);
        } else {
            $mobile = $mobiles;
        }
        $data = [
            'account'   => $this->user,
            'password'   => $this->password,
            'msg'   => "【" . $this->sign . "】" . $content,
            'phone' => $mobile,
            'extend'    => $this->extno
        ];

        $res = json_decode(
            $this->client->request(
                'post', 'msg/send/json', [
                'json'  => $data
                ]
            )->getBody(), 1
        );

        $data = [];
        if(is_array($mobiles)) {
            foreach ($mobiles as $mobile){
                array_push(
                    $data, [
                    'mobile'    => $mobile,
                    'content'   => $content,
                    'ip'        => Request::instance()->ip(),
                    'code'      => $res['code'],
                    'msgid'     => $res['msgId'],
                    'res'       => serialize($res),
                    'created_at'=> time()
                    ]
                );
            }
        } else {
            array_push(
                $data, [
                'mobile'    => $mobiles,
                'content'   => $content,
                'ip'        => Request::instance()->ip(),
                'code'      => $res['code'],
                'msgid'     => $res['msgId'],
                'res'       => serialize($res),
                'created_at'=> time()
                ]
            );
        }

        Db::name('v9_gn_sms')->insertAll($data);
        return $res['code'];
    }

    /**
     * 查询余额
     */
    public function balance()
    {
        $data = [
            'account'   => sysconf('sms_cl_user'),
            'password'   => sysconf('sms_cl_password'),
        ];

        $res = $this->client->post(
            'msg/balance/json', [
            'json'  => $data
            ]
        );
        return json_decode($res->getBody(), 1);
    }
}
