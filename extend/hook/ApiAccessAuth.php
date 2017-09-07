<?php
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace hook;

use think\Cache;
use think\Db;
use think\exception\HttpResponseException;
use think\Request;

/**
 * Class ApiAccessAuth
 *
 * @package hook
 * access_token 验证
 */
class ApiAccessAuth
{
    /**
     * 当前请求对象
     *
     * @var Request
     */
    protected $request;

    protected $except = [
        'api/access_token'
    ];
    /**
     * 行为入口
     *
     * @param $params
     */
    public function run(&$params)
    {
        $this->request = Request::instance();
        if(!in_array($this->request->pathinfo(), $this->except)) {
            if(!$this->request->has('access_token')) {
                $result = [
                    'err_code'  => '403',
                    'err_msg'   => 'access_token not provider.',
                ];
                throw new HttpResponseException(json($result));
            }
            if(!Cache::has($this->request->get('access_token'))) {
                $result = [
                    'err_code'  => '404',
                    'err_msg'   => 'access_token expired.',
                ];
                throw new HttpResponseException(json($result));
            }
            $app = Cache::get($this->request->get('access_token'));
            $user = Db::name('system_user')->field('id,username,nickname,qq,mail,phone,desc,status')->where(['id' => $app['user_id']])->find();
            // todo 如果接口访问变慢的话,将下面注释, 减少一次数据库查询, 影响为ip白名单滞后,具体表现为重新申请token后才会生效
            $app = Db::name('auth_client')->where(['id' => $app['id']])->find();
            $this->request->bind('app', $app);
            $this->request->bind('user', $user);
        }
    }
}
