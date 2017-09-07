<?php
/**
 * User: Jasmine2
 * Date: 2017-7-19 11:53
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace hook;

/**
 * Class ApiLog
 *
 * @package hook
 * 接口日志保存
 */
use think\Db;
use PDOException;
use think\Request;
use think\response\Json;

class ApiLog
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
        if($params instanceof Json) {
            $app = $this->request->app;
        }
        $resource = $this->request->pathinfo();
        $data = [
            'app_id'        => isset($app['id'])?$app['id']:0,
            'resource_id'   => $resource,
            'request_data'  => \GuzzleHttp\json_encode($this->request->request()),
            'response_data' => $params->getContent(),
            'create_at'     => time()
        ];
        try{
            Db::name('api_logs_' . date('Ymd'))->insert($data);
        } catch (PDOException $e){
            Db::query("create table api_logs_" . date('Ymd') . " like api_logs");
            Db::name('api_logs_' . date('Ymd'))->insert($data);
        }
    }
}
