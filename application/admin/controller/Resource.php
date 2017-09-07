<?php
/**
 * User: Jasmine2
 * Date: 2017-7-18 15:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Cache;
use think\Db;
use service\DataService;
use service\LogService;
/**
 * Class Resource
 *
 * @package app\admin\controller
 * 接口资源
 */
class Resource extends BasicAdmin
{
    public $table = 'auth_resource';

    public function index()
    {
        $this->title = '应用资源管理';
        $db = Db::name($this->table)->order('create_at desc,id asc');
        // 应用搜索条件
        $get = $this->request->get();
        foreach (['name'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        return parent::_list($db, 1);
    }

    public function add() 
    {
        return $this->_form($this->table, 'form');
    }
    public function edit() 
    {
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if($this->request->isPost()) {
            if(!isset($data['id'])) {
                $name = $data['name'];
                $path = $data['path'];
                if(Db::name($this->table)->field('id')->where(['name' => $name])->find()) {
                    $this->error('资源名称已经被占用');
                }
                if(Db::name($this->table)->field('id')->where(['path' => $path])->find()) {
                    $this->error('资源路劲不唯一');
                }
                $data['create_at'] = time();
            }
            $data['update_at'] = time();
        }
    }

    protected function _form_result()
    {
        $this->cache();
    }
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('资源管理', session('user.username') . "删除了资源[".input('post.id')."]");
            $this->cache();
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
    /**
     * 禁用
     */
    public function forbid() 
    {
        if (DataService::update($this->table)) {
            LogService::write('资源管理', session('user.username') . "禁用了资源[".input('post.id')."]");
            $this->cache();
            $this->success("禁用成功！", '');
        }
        $this->error("禁用失败，请稍候再试！");
    }

    /**
     * 启用
     */
    public function resume() 
    {
        if (DataService::update($this->table)) {
            LogService::write('资源管理', session('user.username') . "启用了资源[".input('post.id')."]");
            $this->cache();
            $this->success("启用成功！", '');
        }
        $this->error("启用失败，请稍候再试！");
    }
    /**
     * 更新缓存
     */
    public function recache()
    {
        $this->cache();
        $this->success("刷新缓存成功！", '');
    }


    /**
     * 缓存数据
     */
    private function cache()
    {
        $data = Db::name($this->table)->field('path')->where(['status' => 99])->select();
        $data = array_column($data, 'path');
        $data[] = 'api/access_token';
        Cache::set('resources', $data , 0);
    }
}
