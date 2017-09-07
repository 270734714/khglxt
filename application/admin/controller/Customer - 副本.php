<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use service\ToolsService;
use think\Db;

/**
 * 客户列表
 * Class Index
 *
 * @package app\admin\controller
 * @author  xulei
 * @date    2017/09/04 10:52
 */
class Customer extends BasicAdmin
{
	public $table = 'v9_gn_customer';
	public $table_dic = 'v9_gn_dic';
	public $table_followup = 'v9_gn_followup';

    public function customer_list() 
    {
      $this->title = '客户列表';
       // 获取到所有GET参数
        $get = $this->request->get();
        // 实例Query对象
        //$db = Db::table('v9_gn_customer')->alias('a')->join('v9_gn_followup b','a.id = b.uid')->select();;
        // 应用搜索条件
        // if (isset($get['key']) && $get['key'] !== '') {
        //      $db->where('name', 'like', "%{$get['key']}%	");    
        // }
        
	 // 实例化并显示
    	return parent::_list();
    }

     /**
     * 客户添加
     */
    public function add(){
    	return $this->_form($this->table, 'form');
    }
    
	/**
     * 用户编辑
     */
    public function edit() 
    {	
        return $this->_form($this->table, 'form');
    }

	/**
     * 删除
     */
    public function del() 
    {
        if (DataService::update($this->table)) {
            $this->success("用户删除成功！", '');
        }
      	  
      	$this->error("用户删除失败，请稍候再试！");
    }

    /**
     * 表单数据默认处理
     *
     * @param array $data
     */
    public function _form_filter(&$data) 
    {
        if ($this->request->isPost()) {
        	//字典替换
        	$data['objective'] = $this->get_dic_name($this->table_dic,'objective',$_POST['objective']);
        	$data['source'] = $this->get_dic_name($this->table_dic,'source',$_POST['source']);
        	$data['types'] = $this->get_dic_name($this->table_dic,'types',$_POST['types']);
        	$data['status'] = $this->get_dic_name($this->table_dic,'status',$_POST['status']);
        	$data['followupk'] = $_POST['followup'];
        	$data['followtime'] = date("Y-m-d",strtotime("+".$_POST['disposals']." day"));
        	$data['disposalsk'] = $_POST['disposals'];	

        	$maxid = Db::name($this->table)->max('id');
        	$data_followup['uid'] = $maxid + 1;
        	$data_followup['followup'] = $_POST['followup'];
        	$data_followup['disposals'] = $_POST['disposals'];
        	
        	Db::name($this->table_followup)->insert($data_followup);

        } else {
        	//查找字典下拉菜单
        	$this->find_dic($this->table_dic,'objective');
        	// $this->find_dic($this->table_dic,'source');
        	// $this->find_dic($this->table_dic,'types');
        	// $this->find_dic($this->table_dic,'status');
        	// $this->find_dic($this->table_dic,'followup');
        	// $this->find_dic($this->table_dic,'disposals');           
        }
    }
    
    //字典下拉菜单
    public function find_dic($db_name,$type){
    	$_menus = Db::name($db_name)->where('type', $type)->order('id asc')->select();
  	
        $_menus[] = ['name' => '请选择', 'id' => '0', 'pid' => '-1'];
        $parents = ToolsService::arr2table($_menus);
 
// echo'<pre>'; 
// print_r($parents); 
// echo'</pre>'; 
// die;        
        $this->assign('menus', $parents);
		//$this->assign($type, $parents);
    }

    //字典替换
    public function get_dic_name($db_name,$field,$id){
    	$data[$field] = Db::name($db_name)->where('id', $id)->field('name')->find(); 
    	return $data[$field] = $data[$field]['name'];
    	//print_r(Db::name($db_name)->getLastSql());die;
    }
}
