<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use app\models\Notice;
use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;
use think\Request;
use think\View;

/**
 * 后台入口
 * Class Index
 *
 * @package app\admin\controller
 * @author  Anyon <zoujingli@qq.com>
 * @date    2017/02/15 10:41
 */
class Index extends BasicAdmin
{

    /**
     * 后台框架布局
     *
     * @return View
     */
    public function index() 
    {
        NodeService::applyAuthNode();
        $list = Db::name('SystemMenu')->where('status', '1')->order('sort asc,id asc')->select();
        $menus = $this->_filterMenu(ToolsService::arr2tree($list));
        $this->assign('title', '系统管理');
        $this->assign('menus', $menus);
        return view();
    }

    /**
     * 后台主菜单权限过滤
     *
     * @param  array $menus
     * @return array
     */
    private function _filterMenu($menus) 
    {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_filterMenu($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (stripos($menu['url'], 'http') === 0) {
                continue;
            } elseif ($menu['url'] !== '#' && auth(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
                $menu['url'] = url($menu['url']);
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 主机信息显示
     *
     * @return View
     */
    public function main() 
    {
        $_version = Db::query('select version() as ver');
        $version = array_pop($_version);

        $this->assign('mysql_ver', $version['ver']);
        return view();
    }
    /**
     * 会员首页
     *
     * @return View
     */
    public function member_index()
    {
        $messages = Db::name('v9_bpm_news')->where('pid', '=', 0)->order('id', 'DESC')->limit(10)->select();
        $notice   = Db::name('v9_bpm_notice')->order('sort', 'AES')->order('id', 'DESC')->limit(10)->select();
        return view(
            '', [
            'messages'  => $messages,
            'notice'    => $notice
            ]
        );
    }
    /**
     * 修改密码
     */
    public function pass() 
    {
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('访问异常！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        } else {
            $data = $this->request->post();
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致，请重新输入！');
            }
            $user = Db::name('SystemUser')->where('id', session('user.id'))->find();
            if (!password_verify($data['oldpassword'], $user['password'])) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            if (DataService::save('SystemUser', ['id' => session('user.id'), 'password' => password_hash($data['password'], PASSWORD_DEFAULT)])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改资料
     */
    public function info() 
    {
        if (intval($this->request->request('id')) === intval(session('user.id'))) {
            return $this->_form('SystemUser', 'user/form');
        }
        $this->error('访问异常！');
    }

    public function view_notice(Request $request)
    {
        $id = $request->get('id');
        $notice = Notice::get($id);
        $notice->views = $notice->views + 1;
        $notice->save();
        return view(
            '', [
            'notice'    => $notice
            ]
        );
    }
}
