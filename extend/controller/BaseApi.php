<?php
/**
 * User: Jasmine2
 * Date: 2017-6-8
 * Time: 12:12
 */

namespace controller;

use think\Request;

/**
 * Class BaseApi
 *
 * @package Controller
 * @author  jasmine2 <youjingqiang@gmail.com>
 */
class BaseApi
{
    use ToolResponse;
    public $para = null;
    public $request;
    public function __construct()
    {
        $request = Request::instance();
        $this->request = $request;
        if($request->has('data')) {
            $this->para = api_decrypt($request->request('data'));
            $request->bind('data', $this->para);
        }
    }
}
