<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 系统日志管理
 * Class User
 *
 * @package app\admin\controller
 * @author  Anyon <zoujingli@qq.com>
 * @date    2017/02/15 18:12
 */
class Log extends BasicAdmin
{

    /**
     * 指定当前数据表
     *
     * @var string
     */
    public $table = 'SystemLog';

    /**
     * 日志列表
     */
    public function index() 
    {
        $this->title = '系统操作日志';
        $this->assign('actions', Db::name($this->table)->group('action')->column('action'));
        $db = Db::name($this->table)->order('id desc');
        $get = $this->request->get();
        foreach (['action', 'content', 'username'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     *
     * @param $data
     */
    protected function _index_data_filter(&$data) 
    {
        $ip = new \Ip2Region();
        foreach ($data as &$vo) {
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '|'], ['', ' '], $vo['isp']);
        }
    }

    /**
     * 日志删除操作
     */
    public function del() 
    {
        if (DataService::update($this->table)) {
            $this->success("日志删除成功！", '');
        }
        $this->error("日志删除失败，请稍候再试！");
    }

}
