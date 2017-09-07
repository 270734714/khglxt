<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace hook;

use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\View;

/**
 * 访问权限管理
 * Class AccessAuth
 *
 * @package hook
 * @date    2017/05/12 11:59
 */
class AccessAuth
{

    /**
     * 当前请求对象
     *
     * @var Request
     */
    protected $request;

    /**
     * 行为入口
     *
     * @param $params
     */
    public function run(&$params) 
    {
        $this->request = Request::instance();
        list($module, $controller, $action) = [$this->request->module(), $this->request->controller(), $this->request->action()];
        $vars = get_class_vars(config('app_namespace') . "\\{$module}\\controller\\{$controller}");
        // 用户登录状态检查
        if ((!empty($vars['checkAuth']) || !empty($vars['checkLogin'])) && !session('user')) {
            if ($this->request->isAjax()) {
                $result = ['code' => 0, 'msg' => '抱歉, 您还没有登录!', 'data' => '', 'url' => url('@admin/login'), 'wait' => 3];
                throw new HttpResponseException(json($result));
            }
            throw new HttpResponseException(redirect('@admin/login'));
        }
        // 访问权限节点检查
        if (!empty($vars['checkLogin']) && !auth("{$module}/{$controller}/{$action}")) {
            $result = ['code' => 0, 'msg' => '抱歉, 您没有权限访问该模块!', 'data' => '', 'url' => '', 'wait' => 3];
            throw new HttpResponseException(json($result));
        }
        // 权限正常, 默认赋值
        $view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $view->assign('classuri', strtolower("{$module}/{$controller}"));
    }

}
