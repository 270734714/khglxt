<?php
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
use service\DataService;
use think\Request;

class Mobileregion extends BasicAdmin
{
    public $table = 'MobileRegion';

    public function index()
    {
        // 设置页面标题
        $this->title = '运营商数据管理';
        // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        $db = Db::name($this->table);
        // 应用搜索条件
        foreach (['mobile'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, '=', $get[$key]);
            }
        }
        // 实例化并显示
        return parent::_list($db);
    }
    /**
     *
     */
    public function add() 
    {
        return $this->_form($this->table, 'form');
    }

    /**
     *
     */
    public function edit() 
    {
        return $this->_form($this->table, 'form');
    }

    public function del() 
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
}
