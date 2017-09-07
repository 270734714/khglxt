<?php
/**
 * User: Jasmine2
 * Date: 2017-7-17 18:16
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use service\LogService;
use think\Cache;
use think\Db;
use service\DataService;

/**
 * Class Client
 */

class Client extends BasicAdmin
{
    public $table = 'auth_client';

    public function index()
    {
        $this->title = '应用管理';
        $db = Db::name($this->table)->order('create_time desc,id asc');
        // 应用搜索条件
        $get = $this->request->get();
        foreach (['app_name'] as $key) {
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
        $users = Db::name('system_user')->field(['username'])->where(['status' => 1])->select();
        $this->assign('users', array_column($users, 'username'));
        if($this->request->isPost()) {
            $data['update_time'] = time();
            if(isset($data['id'])) {
                $data['create_time'] = time();
                LogService::write('应用管理', session('user.username') . "修改了应用[".input('post.id')."]");
            } else {
                $data['client_id'] = get_random_string('sd_', 16);
                $data['client_secret'] = get_random_string();
                LogService::write('应用管理', session('user.username') . "添加了应用" . $data['app_name']);
            }
            $user = Db::name('system_user')->field(['id'])->where(['username' => $data['user_id']])->find();
            $data['user_id'] = $user['id'];
        }
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('应用管理', session('user.username') . "删除了应用[".input('post.id')."]");
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 锁定
     */
    public function forbid() 
    {
        if (DataService::update($this->table)) {
            LogService::write('应用管理', session('user.username') . "锁定了应用[".input('post.id')."]");
            $this->success("锁定成功！", '');
        }
        $this->error("锁定失败，请稍候再试！");
    }

    /**
     * 启用
     */
    public function resume() 
    {
        if (DataService::update($this->table)) {
            LogService::write('应用管理', session('user.username') . "启用了应用[".input('post.id')."]");
            $this->success("启用成功！", '');
        }
        $this->error("启用失败，请稍候再试！");
    }
    /**
     * 重置app_secret
     */
    public function reapp_secret() 
    {
        $id = $this->request->post('id', false);
        if($id) {
            $s = Db::name($this->table)->where(['id' => $id])->update(
                [
                'client_secret'    => get_random_string(),
                'update_time'   => time()
                ]
            );
            if($s === 1) {
                LogService::write('应用管理', session('user.username') . "重置app_secret成功[".input('post.id')."]");
                $this->success("重置app_secret成功！", '');
            } else {
                $this->error("重置失败，请稍候再试！");
            }
        }
        $this->error("应用不存在！");
    }

    /**
     * @return array|string
     * 应用资源
     */
    public function app_info()
    {
        $this->title = '应用资源';
        $db = Db::name('auth_app_resource')->alias('a')
            ->join('auth_resource b', 'a.resource_id=b.id', 'LEFT')
            ->where('a.app_id', '=', $this->request->get('id'));
        return parent::_list($db);
    }

    /**
     * @return \think\response\View
     * 添加资源
     */
    public function add_resource()
    {
        $app_id = $this->request->get('app_id');
        if($this->request->isPost()) {
            $post = $this->request->post();
            $post['app_id'] = $app_id;
            $post['expire_at'] = strtotime($post['expire_at']);
            Db::name('auth_app_resource')->insert($post);
            $this->cache_client($app_id);
            $this->success("配置成功！", '');
        } else {
            $authed_resource = Db::name('auth_app_resource')->where('app_id', '=', $app_id)->column('resource_id');
            $resources = Db::name('auth_resource')->whereNotIn('id', $authed_resource)->select();
            return view(
                '', [
                'vo'    => [],
                'resource'  => $resources
                ]
            );
        }
    }

    /**
     * @return \think\response\View
     * 编辑资源
     */
    public function edit_resource()
    {
        $app_id = $this->request->get('app_id');
        $resource_id = $this->request->get('id');
        if($this->request->isPost()) {
            $post = $this->request->post();
            isset($post['expire_at'])?$post['expire_at'] = strtotime($post['expire_at']):null;
            Db::name('auth_app_resource')->where(
                [
                'app_id'    => $app_id,
                'resource_id'   => $resource_id
                ]
            )->update(array_filter($post));
            $this->cache_client($app_id);
            $this->success("配置成功！", '');
        } else {
            $resources = Db::name('auth_resource')->where('status', '=', 99)->select();

            $app_resource = Db::name('auth_app_resource')->where('app_id', '=', $app_id)->where('resource_id', '=', $resource_id)->find();
            return view(
                'add_resource', [
                'vo'    => $app_resource,
                'resource'  => $resources
                ]
            );
        }
    }

    /**
     * 删除资源
     */
    public function del_resource()
    {
        $app_id = $this->request->get('app_id');
        $resource_id = $this->request->post('id');
        Db::name('auth_app_resource')->where('app_id', '=', $app_id)->where('resource_id', '=', $resource_id)->delete();
        $this->cache_client($app_id);
        $this->success("删除成功！", '');
    }

    /**
     * 刷新缓存
     */
    public function app_recache()
    {
        $app_id = $this->request->post('id');
        $this->cache_client($app_id);
        $this->success("刷新缓存成功！", '');
    }


    /**
     * @param $app_id
     * 刷新缓存
     */
    private function cache_client($app_id)
    {
        $app_info = Db::query('SELECT a.app_id,a.resource_id,a.type,a.amount,a.expire_at,b.path from auth_app_resource a LEFT JOIN auth_resource b on a.resource_id=b.id WHERE a.app_id=?', [$app_id]);
        $app_prices = [];
        foreach ($app_info as $item){
            $app_prices[$item['path']] = [
                'id'        => $item['resource_id'],
                'type'      => $item['type'],
                'amount'    => $item['amount'],
                'expire_at' => $item['expire_at']
            ];
        }
        Cache::set(config('app_acl_prefix') . $app_id, $app_info, 0);
        Cache::set(config('app_price_prefix') . $app_id, $app_prices, 0);
    }

    /**
     * IP 白名单操作
     */
    public function update_ip_white_list()
    {
        $vo = Db::name($this->table)->where(['id' => $this->request->get('id')])->find();
        if($this->request->isPost()) {
            Db::name($this->table)->where(['id' => $this->request->get('id')])->update(
                [
                'ip_white_list' => $this->request->post('ip_white_list')
                ]
            );
            $this->success("保存成功！", '');
        }
        return view(
            'update_ip_white_list', [
            'vo'    => $vo
            ]
        );
    }
}
