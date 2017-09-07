<?php
/**
 * User: Jasmine2
 * Date: 2017-6-24 15:18
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
use think\Request;
use service\DataService;
class Bank extends BasicAdmin
{
    public $table = 'v9_gn_banklist';
    public function index()
    {
        $this->title = '银行列表维护';
        $db = Db::name($this->table)->order('sort desc,id desc');
        $get = $this->request->get();
        foreach (['bank_code', 'bank_name'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        return parent::_list($db, true);
    }
    public function _form_filter(&$data)
    {
        if($this->request->isPost()) {
            $data['bank_code'] = strtoupper($data['bank_code']);
        }
        $categorys = Db::table('system_dictionary')->where(['category' => 4])->select();
        $this->assign('statues', $categorys);
    }
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function del(Request $request = null)
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
}
