<?php
/**
 * User: Jasmine2
 * Date: 2017-6-17
 * Time: 17:57
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
use think\Request;
use service\DataService;
class Dictionarycategory extends BasicAdmin
{
    public $table = 'system_dictionary_category';

    public function index()
    {
        $this->title = '数据字典分类';
        $db = Db::name($this->table)->order('id asc');
        return parent::_list($db, false);
    }

    public function _form_filter(&$data)
    {
        if($this->request->isPost() && !isset($data['id'])) {
            $data['create_at'] = time();
        }
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
            $this->success("分类删除成功！", '');
        }
        $this->error("分类删除失败，请稍候再试！");
    }
}
