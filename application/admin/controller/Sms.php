<?php
/**
 * User: Jasmine2
 * Date: 2017-6-25 18:51
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
class Sms extends BasicAdmin
{
    public $table = 'v9_gn_sms';

    public function index()
    {
        $this->title = '短信管理';
        $db = Db::name($this->table)->order('id asc');
        return parent::_list($db, true);
    }

    protected function _index_data_filter(&$data)
    {
        $ip = new \Ip2Region();
        foreach ($data as &$vo) {
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '|'], ['', ' '], $vo['isp']);
            if($vo['code'] == '0') {
                $vo['resp'] = '<span class="layui-btn layui-btn-mini">成功</span>';
            } else {
                $resp = unserialize($vo['res']);
                $vo['resp'] = $resp['errorMsg'];
            }
        }
    }
}
