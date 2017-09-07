<?php
/**
 * User: Jasmine2
 * Date: 2017-7-6 18:27
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use app\models\Notice as NoticeModel;
class Notice extends BasicAdmin
{
    public $table = 'v9_bpm_notice';
    public function index()
    {
        $this->title = '已发布通知';
        $db = NoticeModel::order('sort desc,id desc');
        return parent::_list($db, true);
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function del()
    {
        if (NoticeModel::where('id', 'in', input('id'))->delete()) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }
}
