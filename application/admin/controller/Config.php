<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use sms\Chuanglan;

/**
 * 后台参数配置控制器
 * Class Config
 *
 * @package app\admin\controller
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     *
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     *
     * @var string
     */
    public $title = '网站参数配置';

    /**
     * 显示系统常规配置
     */
    public function index() 
    {
        if (!$this->request->isPost()) {
            $this->assign('title', $this->title);
            return view();
        }
        foreach ($this->request->post() as $key => $vo) {
            sysconf($key, $vo);
        }
        LogService::write('系统管理', '修改系统配置参数成功');
        $this->success('数据修改成功！', '');
    }

    /**
     * 文件存储配置
     */
    public function file() 
    {
        $this->assign(
            'alert', [
            'type'    => 'danger',
            'title'   => '操作提示',
            'content' => '文件引擎参数影响全局文件上传功能，请勿随意修改！'
            ]
        );
        $this->title = '文件存储配置';
        return $this->index();
    }

    /**
     * 短信渠道配置
     */
    public function sms()
    {
        $this->assign(
            'alert', [
            'type'    => 'danger',
            'title'   => '操作提示',
            'content' => '短信参数配置影响全局短信发送功能，请勿随意修改！'
            ]
        );
        $this->title = '短信渠道配置';
        return $this->index();
    }
    /**
     * 短信发送测试
     */
    public function sms_test()
    {
        $ret = send_sms($this->request->post('mobile'), '这是一条测试短信');
        if($ret == 0) {
            $this->success("短信发送成功!", '');
        } else {
            $this->error("短信发送失败[$ret]");
        }
    }

    /**
     * 短信发送测试
     */
    public function sms_balance()
    {
        $sms = new Chuanglan("other");
        $balance = $sms->balance();
        $this->error("短信剩余条数[".$balance['balance']."]");
    }
}
