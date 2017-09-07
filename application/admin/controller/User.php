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
use service\LogService;
use service\ToolsService;
use think\Db;

/**
 * 系统用户管理控制器
 * Class User
 *
 * @package app\admin\controller
 * @author  Anyon <zoujingli@qq.com>
 * @date    2017/02/15 18:12
 */
class User extends BasicAdmin
{

    /**
     * 指定当前数据表
     *
     * @var string
     */
    public $table = 'SystemUser';

    /**
     * 用户列表
     */
    public function index() 
    {
        // 设置页面标题
        $this->title = '系统用户管理';
        // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        $db = Db::name($this->table)->where('is_deleted', '0');
        // 应用搜索条件
        foreach (['username', 'phone'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        // 实例化并显示
        return parent::_list($db);
    }
    /**
     * 列表数据处理
     *
     * @param array $data
     */
    protected function _index_data_filter(&$data) 
    {
        foreach ($data as &$vo) {
            $vo['region'] = \GuzzleHttp\json_decode($vo['mobile_region'], 1);
        }
    }
    /**
     * 授权管理
     *
     * @return array|string
     */
    public function auth() 
    {
        return $this->_form($this->table, 'auth');
    }

    /**
     * 用户添加
     */
    public function add() 
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户编辑
     */
    public function edit() 
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 用户密码修改
     */
    public function pass() 
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', false);
            return $this->_form($this->table, 'pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致！');
        }
        if (DataService::save($this->table, ['id' => $data['id'], 'password' => password_hash($data['password'], PASSWORD_DEFAULT)], 'id')) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }

    /**
     * 表单数据默认处理
     *
     * @param array $data
     */
    public function _form_filter(&$data) 
    {
        if ($this->request->isPost()) {
            if (isset($data['authorize']) && is_array($data['authorize'])) {
                $data['authorize'] = join(',', $data['authorize']);
            }
            $prefix = $data['phone'];
            if($d = Db::table('mobile_region')->where(['mobile' => substr($prefix, 0, 7)])->find()) {
                $data['mobile_region'] = \GuzzleHttp\json_encode($d);
            }
            if (isset($data['id'])) {
                unset($data['username']);
                LogService::write('用户管理', "编辑用户");
            } elseif (Db::name($this->table)->where('username', $data['username'])->find()) {
                $this->error('用户账号已经存在，请使用其它账号！');
            }
        } else {
            // 上级菜单处理
            $_menus = Db::name($this->table)->where('status', '1')->order('create_at desc,id desc')->select();
            $_menus[] = ['username' => '请选择', 'id' => '0', 'pid' => '-1'];
            $parents = ToolsService::arr2table($_menus);
            foreach ($parents as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) {
                    unset($parents[$key]);
                    continue;
                }
                if (isset($vo['pid'])) {
                    $current_path = "-{$vo['pid']}-{$vo['id']}";
                    if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
                        unset($parents[$key]);
                    }
                }
            }
            $data['authorize'] = explode(',', isset($data['authorize']) ? $data['authorize'] : '');

            $this->assign('menus', $parents);
            $this->assign('authorizes', Db::name('SystemAuth')->select());
        }
    }

    /**
     * 删除用户
     */
    public function del() 
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止删除！');
        }
        if (DataService::update($this->table)) {
            $this->success("用户删除成功！", '');
        }
        $this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function forbid() 
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        if (DataService::update($this->table)) {
            $this->success("用户禁用成功！", '');
        }
        $this->error("用户禁用失败，请稍候再试！");
    }

    /**
     * 用户禁用
     */
    public function resume() 
    {
        if (DataService::update($this->table)) {
            $this->success("用户启用成功！", '');
        }
        $this->error("用户启用失败，请稍候再试！");
    }

}
