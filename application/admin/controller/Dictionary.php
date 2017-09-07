<?php
/**
 * User: Jasmine2
 * Date: 2017-6-17
 * Time: 17:57
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Cache;
use think\Db;
use think\Request;
use service\DataService;
class Dictionary extends BasicAdmin
{
    public $table = 'system_dictionary';

    public function index()
    {
        $this->title = '数据字典';
        $db = Db::name($this->table)->order('category desc,id asc');
        return parent::_list($db);
    }

    public function _form_filter(&$data)
    {
        if($this->request->isPost()) {
            if(isset($data['id']) && !$data['id']) {
                $data['create_at'] = time();
            }
            $category = Db::table('system_dictionary_category')->where(['id' => $data['category']])->find();
            $data['category_name'] = $category['value'];
        }
        $categorys = Db::table('system_dictionary_category')->select();
        $this->assign('categorys', $categorys);
    }

    public function _form_result(&$data)
    {
        $dic = Db::table($this->table)->order('category desc,id asc')->select();
        $_dic = [];
        foreach ($dic as $item){
            $_dic[$item['category']][$item['value']] = $item['desc'];
        }
        Cache::set('dic', $_dic, 0);
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
            $this->success("字典删除成功！", '');
        }
        $this->error("字典删除失败，请稍候再试！");
    }
}
