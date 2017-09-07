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
use service\NodeService;
use think\Db;

/**
 * 系统登录控制器
 * class Login
 *
 * @package app\admin\controller
 * @author  Anyon <zoujingli@qq.com>
 * @date    2017/02/10 13:59
 */
class Login extends BasicAdmin
{

    /**
     * 默认检查用户登录状态
     *
     * @var bool
     */
    public $checkLogin = false;

    /**
     * 默认检查节点访问权限
     *
     * @var bool
     */
    public $checkAuth = false;

    /**
     * 控制器基础方法
     */
    public function _initialize() 
    {
        if (session('user') && $this->request->action() !== 'out') {
            $this->redirect('@admin');
        }
    }

    /**
     * 用户登录
     *
     * @return string
     */
    public function index() 
    {
        if ($this->request->isGet()) {
            $this->assign('title', '用户登录');
            return $this->fetch('');
        } else {
            $username = $this->request->post('username', '', 'trim');
            $password = $this->request->post('password', '', 'trim');
            (empty($username) || strlen($username) < 4) && $this->error('登录账号长度不能少于4位有效字符!');
            (empty($password) || strlen($password) < 4) && $this->error('登录密码长度不能少于4位有效字符!');
            $user = Db::name('SystemUser')->where('username', $username)->find();
            empty($user) && $this->error('登录账号不存在，请重新输入!');
            !password_verify($password, $user['password']) && $this->error('登录密码与账号不匹配，请重新输入!');
            Db::name('SystemUser')->where('id', $user['id'])->update(['login_at' => ['exp', 'now()'], 'login_num' => ['exp', 'login_num+1']]);
            session('user', $user);
            !empty($user['authorize']) && NodeService::applyAuthNode();
            LogService::write('系统管理', '用户'.$username.'登录系统成功');
            $this->success('登录成功，正在进入系统...', '@admin');
        }
    }

    /**
     * 退出登录
     */
    public function out() 
    {
        LogService::write('系统管理', '用户'.session('user.username').'退出系统成功');
        session('user', null);
        session_destroy();
        $this->success('退出登录成功！', '@admin/login');
    }
}
