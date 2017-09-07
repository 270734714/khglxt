<?php

/**
 * User: xulei
 * Date: 2017-9-3 16:55
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use service\ToolsService;
use think\Db;
use think\Session;

/**
 * 客户列表
 */
class Customer extends BasicAdmin{
	
    public $table = 'v9_gn_customer';
	public $table_dic = 'v9_gn_dic';
	public $table_followup = 'v9_gn_followup';
    public $table_serve = 'v9_gn_cumstorserve';

    public static function auth(){
        return  Session::get('user.authorize');
    }

    public function customer_list(){
// print_r(Session::get());die;
        $this->title = '客户列表';
        // 获取到所有GET参数
        $get = $this->request->get();
        //权限
        if(self::auth() == '1'){
            $where = '1 = 1';
            $this->assign('authorizes_bj', Db::table('System_Auth')->select());
            $this->assign('authorizes', Db::table('System_Auth')->select());
        }else if(self::auth() == '2'){
            $where = 'delstatus = 1  and (status != 13 or status IS NULL) and operatorid = '.Session::get('user.id');   
           
            $this->assign('authorizes', Db::table('System_Auth')->select());
            $this->assign('authorizes_bj', Db::table('System_Auth')->select());
        }else if(self::auth() == '3'){
            $where = 'delstatus = 1 and (status != 13 or status IS NULL)';          
        }else{
            $where = 'delstatus = 1';
        }

        $db = Db::table($this->table)->where($where)->order('id desc');
        //搜索
        if (isset($get['key']) && $get['key'] !== '') {
            $db->where('name', 'like', "%{$get['key']}%");
        }
	 // 实例化并显示
    	return parent::_list($db);
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
    public function edit(){	
        return $this->_form($this->table, 'form');
    }

	/**
     * 删除
     */
    public function del(){

        if(DB::table($this->table)->whereIn('id',$_POST['id'])->update(['delstatus' => '0'])){
            $this->success("用户删除成功！", '');
        } 

    }

    //客户列表跟进
    public function followup(){
         if ($this->request->isPost()) {
            $_POST['uid'] = $_GET['id'];
            $_POST['createtime'] = time();
            $_POST['operatorid'] = Session::get('user.id');

            $flog = Db::table($this->table_followup)->where('uid',$_GET['id'])->find();  

            if($flog){
                Db::table($this->table_followup)->where('uid',$_GET['id'])->update($_POST);
            }else{
                Db::table($this->table_followup)->insert($_POST);
            }
            
            //更改客户状态和跟进时间
            $this->changeFollowTimeStatus($_GET['id'],$_POST['disposals']);  
          

            $this->success('客户跟进操作成功！', '');
            break;
         }else{
            $this->assign('title', '客户跟进');

            return $this->_form($this->table_followup, 'followup' , 'uid');
        }
    }

    //跟进列表
    public function customer_followup(){
        
        $this->title = '客户跟进列表';
        // 获取到所有GET参数
        $get = $this->request->get();
        
        $db = Db::table($this->table_followup)->order('id desc');
       
        //搜索
        if (isset($get['key']) && $get['key'] !== '') {
            $db->where('uid', 'like', "%{$get['key']}%");
        }

     // 实例化并显示
        return parent::_list($db);
    }

    public function addfollowup(){
        $get = $this->request->get();
        $db = Db::table($this->table_followup)->order('id desc');
        $disposalss = Db::table($this->table_dic)->where('type', 'disposals')->select();
        $this->assign('disposalss', $disposalss);  

        if(isset($get['id'])){
            if($_POST){
                $uid = Db::table($this->table_followup)->where('id',$get['id'])->value('uid');

                //更改客户状态和跟进时间
                $this->changeFollowTimeStatus( $uid,$_POST['disposals']);  

                Db::table($this->table_followup)->where('id',$get['id'])->update($_POST);
                $this->success('客户跟进操作成功！', 'Customer/customer_followup');
                break;

            }

        }
     // 实例化并显示
        return parent::_list($db);
    }

    /**
     * 表单数据默认处理
     *
     * @param array $data
     */
    public function _form_filter(&$data) 
    {
        if ($this->request->isPost()) {
            if(!is_numeric($data['tel'])){
                $this->error('电话号码有误,请检查！');
            }
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                 $this->error('邮箱格式有误,请检查！');
            }

            $data['operatorid'] = Session::get('user.id');
        } else {
            $objectives = Db::table($this->table_dic)->where('type', 'objective')->select();
            $sources = Db::table($this->table_dic)->where('type', 'source')->select();
            $typess = Db::table($this->table_dic)->where('type', 'types')->select();
            $statuss = Db::table($this->table_dic)->where('type', 'status')->select();
            $followups = Db::table($this->table_dic)->where('type', 'followup')->select();
            $disposalss = Db::table($this->table_dic)->where('type', 'disposals')->select();
        	$this->assign('objectives', $objectives);
            $this->assign('sources', $sources);
            $this->assign('typess', $typess);
            $this->assign('statuss', $statuss);
            $this->assign('followups', $followups);
            $this->assign('disposalss', $disposalss);      

        }
    }

    //更改客户状态和跟进时间
    public function changeFollowTimeStatus($id,$disposals){   

        if($disposals === '8'){
            Db::name('v9_gn_customer')->where('id',$id)->update(array('followtime' => null));
            Db::name('v9_gn_customer')->where('id',$id)->update(array('status'=>'13'));
            Db::name('v9_gn_followup')->where('uid',$id)->delete();            
        }else{
            Db::name('v9_gn_customer')->where('id',$id)->update(array('followtime' => strtotime("+".$disposals." day")));
            Db::name('v9_gn_customer')->where('id',$id)->update(array('status'=>'12'));
        }    
    }


    //客户服务申请
    public function customer_apply(){

        if(empty($_GET['id'])){
            $this->error('没有选择服务客户，请检查！');
            break;
        }
        $id = $_GET['id'];
        $users = Db::table($this->table)->whereIn('id',$id)->select();  
       
        $userNames = '';
       
        foreach ($users as $key => $value) {
            $userNames.= $value['name'].' , ';
        }
        $userNames = substr($userNames,0,strlen($userNames)-2); 

        $types = Db::table($this->table_dic)->where('type', 'servtype')->select();
        $statuss = Db::table($this->table_dic)->where('type', 'servstutas')->select();
        $this->assign('types',$types);
        $this->assign('statuss',$statuss);
        $this->assign('userNames',$userNames);
        $this->assign('userNameIds',$id);
       
        return $this->fetch();
    }

    //添加服务申请
    public function customer_apply_add(){
        $ids_arr = explode(',', $_POST['usernameids']);

        $data = array();
        for($i=0;$i<count($ids_arr);$i++){
            $_POST['servedman'] = $ids_arr[$i];
            $data[] = $_POST;
            foreach ($data as $key => $value) {
                unset($data[$key]['usernameids']);
                $data[$key]['serveid'] = date('Ymd').str_pad(mt_rand(1, 99999),5,'0',STR_PAD_LEFT);//生成服务号
            }
        }

        Db::table($this->table_serve)->insertAll($data);
        $this->success('操作成功','Customer/customer_list');
    }


    //查看
    public function customer_info(){
    
        $id = $this->request->get('id');
        $customer = Db::table($this->table)->alias('a')->join('v9_gn_followup b','a.id = b.uid')->where('a.id',$id)->find();    
        $apply_arr = Db::table($this->table_serve)->where('servedman',$id)->select();  

        $this->assign('apply_arr',$apply_arr);
        $this->assign('customer',$customer);
        return $this->fetch();
    } 

    //客户服务申请列表
    public function customer_apply_list(){
        $this->title = '客户服务申请列表';
        // 获取到所有GET参数
        $get = $this->request->get();
        
        $db = Db::table($this->table_serve)->order('id desc');
        
        //搜索
        if (isset($get['key']) && $get['key'] !== '') {
            $db->where('applicant', 'like', "%{$get['key']}%");
        }
     // 实例化并显示
        return parent::_list($db);
    }

    // 助理确认服务申请
    public function ajax_customer_apply(){
    //     if(empty($_POST['flog'])){
    //         $_POST['flog'] = '1';
    //     }
    // print_r($_POST['flog']);die;    
        if(is_array($_POST['id'])){
            $ids = implode(',', $_POST['id']);
        }else{
            $ids = $_POST['id'];
        }
        // flog 0 取消 1确认
        if(Db::table($this->table_serve)->whereIn('id',$ids)->update(['servcompleted' => $_POST['flog']])){
            echo '1';
        }else{
            echo '0';
        }
    }

    public function servcompleted(){
        $this->title = '客户服务申请确认列表';
        // 获取到所有GET参数
        $get = $this->request->get();
        
        
        $where = '1 = 1';
       

        $db = Db::table($this->table_serve)->where($where)->order('id desc');
        //搜索
        if (isset($get['key']) && $get['key'] !== '') {
            $db->where('name', 'like', "%{$get['key']}%");
        }
     // 实例化并显示
        return parent::_list($db);
    }
}

